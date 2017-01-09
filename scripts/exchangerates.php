<?
require_once('../vendor/init.php');

function exchange_rate($currencies, $date){
  $ep = "https://api.fixer.io/".$date."?base=USD&symbols=".implode(",",$currencies);
  $resp = file_get_contents($ep);
  $resp = json_decode($resp, true);
  $missing = array();
  $out["date"] = $date;
  $out["rates"] = $resp["rates"];
  $out["rates"]["USD"] = 1;
  foreach($currencies as $c){
    if(!isset($resp["rates"][$c]) && $c != "USD"){
      $missing[] = $c;
    }
  }
  if(!empty($missing)){
    $cs = implode(",", $missing);
    // This is a rate-limited API which contains currencies not in the fixerio set and only converts to USD.
    global $CURRENCYAPI;
    $ep = "http://apilayer.net/api/historical?access_key=$CURRENCYAPI&date=$date&currencies=$cs&format=1";
    $rates = file_get_contents($ep);
    $rates = json_decode($rates, true);
    foreach($missing as $currency){
      if(isset($rates["quotes"]["USD".$currency])){
        $out["rates"][$currency] = $rates["quotes"]["USD".$currency];
      }
    }
  }
  return $out;
}

function currency_from_cost($cost){
  $s = structure_cost($cost);
  return $s["currency"];
}

function posts_between($from, $to){
  // Fetch all acquire posts from triplestore.
  // Replace this with retreiving posts your way.
  global $ep;
  $q = construct_between($from->format(DATE_ATOM), $to->format(DATE_ATOM));
  $posts = execute_query($ep, $q);
  $out = array();
  if($posts){
    foreach($posts as $uri => $post){
      if(has_type(array($uri=>$post), 'asext:Acquire')){
        $out[$uri] = $post;
      }
    }
  }
  return $out;
}

function get_rates($from, $to, $current){
  $posts = posts_between($from, $to);

  // Don't re-fetch rates I already have stored
  $exclude = array();
  if(file_exists($current)){
    $ar = json_decode(file_get_contents($current), true);
    $exclude = array_keys($ar);
  }
  
  $currencies = array();
  $rates = array();
  foreach($posts as $uri => $data){
    // Update this to match what your posts look like, including how to get the currency code from each one.
    $date = get_value(array($uri => $data), 'as:published');
    $date = new DateTime($date);
    $d = $date->format("Y-m-d");
    if(!in_array($d, $exclude)){
      $code = currency_from_cost(get_value(array($uri => $data), 'asext:cost'));
      if(!isset($currencies[$d]) || !in_array($code, $currencies[$d])){
        $currencies[$d][] = $code;
      }
    }
  }
  foreach($currencies as $date => $curs){
    $r = exchange_rate($curs, $date);
    $rates[$date] = $r["rates"];
  }
  return $rates;
}

function write_rates($rates, $fn){
  if(file_exists($fn)){
    $existing = json_decode(file_get_contents($fn), true);
    if(is_array($existing)){
      $rates = array_merge($existing, $rates);
    }
  }
  $json = json_encode($rates);
  file_put_contents($fn, $json);
  header('Content-Type: application/json');
  echo $json;
}

$from = new DateTime("2016-01-01");
$to = new DateTime("2017-01-10");
$current = "rates.json";
$rates = get_rates($from, $to, $current);
write_rates($rates, $current);
?>
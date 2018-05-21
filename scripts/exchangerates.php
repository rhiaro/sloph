<?
require_once('../vendor/init.php');
require_once('../vendor/cashcache/cashcache.php');

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

function currency_from_cost($cost){
  $s = structure_cost($cost);
  return $s["currency"];
}

function convert_all($ep, $posts){
  
  foreach($posts as $uri => $data){

    $date = get_value(array($uri=>$data), 'as:published');
    $date = new DateTime($date);
    $datef = $date->format("Y-m-d");
    
    $cost = structure_cost(get_value(array($uri => $data), 'asext:cost'));
    $amount = $cost["value"];
    $currency = $cost["currency"];

    if($amount == "0"){
      $usd = $eur = $gbp = 0;
    }else{

      if($currency == "EUR"){
        $eur = $amount;
      }else{

        $existing = read_rates($date);
        if(!isset($existing["EUR"][$currency])){
          $rates = get_fixer_deprecated($date, $currency);
          if(!array_key_exists($currency, $rates)){
            if(!isset($existing["USD"][$currency])){
              $usdrates = get_currencylayer_rates($date, $currency);
              write_rates($date, $usdrates, "USD");
            }
          }
          write_rates($date, $rates);
        }
        
        $eur = convert_any_to_eur($amount, $currency, $date);
      }
      $usd = convert_eur_to_any($eur, "USD", $date);
      $gbp = convert_eur_to_any($eur, "GBP", $date);
    }

    echo "<p><strong>".$date->format("Y-m-d H:i:s").": <a href='$uri' target='_blank'>$uri</a></strong></p>";
    echo "<p>".implode(" ",$cost)."</p>";
    echo "<p>USD: $usd (";
    $rusd = insert_conversion($ep, $uri, "USD", $usd);
    if($rusd){ echo "inserted"; }else{ echo "failed"; }
    echo ") | EUR: $eur (";
    $reur = insert_conversion($ep, $uri, "EUR", $eur);
    if($reur){ echo "inserted"; }else{ echo "failed"; }
    echo ") | GBP: $gbp (";
    $rgbp = insert_conversion($ep, $uri, "GBP", $gbp);
    if($rgbp){ echo "inserted"; }else{ echo "failed"; }
    echo ")</p>";
    echo "<hr/>";
  }

}

function insert_conversion($ep, $uri, $currency, $amount){
  if($currency == "USD"){
    $p = "asext:amountUsd";
  }elseif($currency == "EUR"){
    $p = "asext:amountEur";
  }elseif($currency == "GBP"){
    $p = "asext:amountGbp";
  }else{
    echo "Unsupported currencty $currency";
    return null;
  }

  $turtle = '<'.$uri.'> '.$p.' """'.$amount.'""" .';
  $q = query_insert($turtle);
  $r = execute_query($ep, $q);
  return $r;
}

if(isset($_GET['from'])){
  $from = $_GET['from'];
}else{
  $from = "24 hours ago";
}

if(isset($_GET['to'])){
  $to = $_GET['to'];
}else{
  $to = "now";
}
$from = new DateTime($from);
$to = new DateTime($to);

?>
<!doctype html>
<html>
  <head><title>Currency conversion</title></head>
  <body>
    <h1>Posts between <?=$from->format("Y-m-d")?> and <?=$to->format("Y-m-d")?></h1>
<?
$posts = posts_between($from, $to);
convert_all($ep, $posts);
?>

  </body>
</html>
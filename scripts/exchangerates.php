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

      $existing = read_rates($date);
      if(!isset($existing["EUR"][$currency])){
        $rates = get_fixer_deprecated($date, $currency);
        write_rates($date, $rates);
      }
      
      $eur = convert_any_to_eur($amount, $currency, $date);
      $usd = convert_eur_to_any($eur, "USD", $date);
      $gbp = convert_eur_to_any($eur, "GBP", $date);
    }

    echo $date->format("Y-m-d H:i:s").": $amount $currency ($usd USD / $eur EUR / $gbp GBP)";
    echo "<hr/>";

  }

}


$from = new DateTime("2016-01-01");
$to = new DateTime("2016-01-23");
$posts = posts_between($from, $to);
convert_all($ep, $posts);
?>
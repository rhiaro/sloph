<?
require_once('../init.php');

function get_novel_data($ep){
  $novels = array(
     "2020" => "https://rhiaro.co.uk/dumping-sky"
    ,"2019" => "https://rhiaro.co.uk/quest-for-brothers-2"
    ,"2018" => "https://rhiaro.co.uk/birds"
    ,"2017" => "https://rhiaro.co.uk/of-the-moon"
    ,"2013" => "https://rhiaro.co.uk/beyond"
    ,"2012" => "https://rhiaro.co.uk/quest-for-brothers"
    ,"2011" => "https://rhiaro.co.uk/touched"
    ,"2009" => "https://rhiaro.co.uk/milos-world"
    ,"2008" => "https://rhiaro.co.uk/dragon-seekers"
  );
  $data = array();
  foreach($novels as $year => $uri){
    $post_q = query_construct($uri);
    $post_r = execute_query($ep, $post_q);
    $data[$year]["uri"] = $uri;
    if($post_r){
      $data[$year] = array(
        "name" => get_value($post_r, "as:name"),
        "content" => get_value($post_r, "as:content"),
      );
      if(get_value($post_r, "asext:wordCount")){
        $data[$year]["wordcount"] = number_format(get_value($post_r, "asext:wordCount"), 0, ".", ",");
      }else{
        $data[$year]["wordcount"] = nanowrimo_total($ep, $year);
      }
    }else{
      $data[$year]["name"] = "No title";
      $data[$year]["content"] = "<p>No data for $year :s</p>";
      $data[$year]["wordcount"] = 0;
    }
  }
  return $data;
}

function nanowrimo_yet($ep){
  $now = new DateTime();
  $day = $now->format("d");
  $month = $now->format("m");
  $year = $now->format("Y");

  $out = array();

  if(($year == "2018" && $month == "10") || ($year != "2018" && $month == "11")){
    $current_count = nanowrimo_total($ep, $year);
    $goal = 1667*$day;
    $c = (int)str_replace(",", "", $current_count);
    $diff = $goal - $c;

    $msg = "(on target!)";
    if($diff < 0){
      $diff = number_format(abs($diff), 0, ".", ",");
      $msg = "<span class=\"wee\">($diff words ahead!)</span>";
    }elseif($diff > 0){
      $diff = number_format($diff, 0, ".", ",");
      $msg = "<span class=\"wee\">($diff behind..)</span>";
    }

    $out["big"] = "YES || $current_count / 50,000 $msg";

    if($year == "2018" && $month == "10"){
      $out["small"] = "Hear me out. NaNoWriMo is usually in November, but in 2018 I'm doing it in October because I have lots of travel plus a 10 day <a href=\"https://dhamma.org\">Vipassana</a> retreat planned in November.";
    }else{
      $out["small"] = "";
    }

  }elseif($year == "2018" && $month == "11"){
    $out["big"] = "YES .. but I did it last month.";
    $out["small"] = "See below. I'm too much away from computers and paper and anything else this month.";

  }else{
    if($month == "12"){
      $year = $year + 1;
    }
    $next = new DateTime("$year-11-01");
    $interval = $now->diff($next);
    $togo = $interval->format("%d");
    $out["big"] = "NO ... $togo days to go";
    $out["small"] = "Find out more and sign up at <a href=\"https://nanowrimo.org\">nanowrimo.org</a>";
  }

  return $out;

}

$years = get_novel_data($ep);
$isit = nanowrimo_yet($ep);

include '../../views/nanowrimo.php';
?>
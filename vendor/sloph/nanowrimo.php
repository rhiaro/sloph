<?
require_once('../init.php');

function get_novel_data($ep){
  $novels = array(
     "2018" => "https://rhiaro.co.uk/birds"
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

$years = get_novel_data($ep);
include '../../views/nanowrimo.php';
?>
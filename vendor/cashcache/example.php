<?
require_once("cashcache.php");

$sample = array(
    array("date" => "2018-05-13T20:33:00+02:00", "amount" => "43", "currency" => "PLN"),
    array("date" => "2017-09-01T13:33:00+02:00", "amount" => "20", "currency" => "BAM"),
    array("date" => "2017-05-13T11:51:00+01:00", "amount" => "16.16", "currency" => "GBP"),
    array("date" => "2018-05-13T11:11:00+04:00", "amount" => "430", "currency" => "QAR"),
    array("date" => "2018-04-13T06:23:00+02:00", "amount" => "5.50", "currency" => "EUR")

);

function convert_posts($posts){
    foreach($posts as $i => $data){
        $date = new DateTime($data["date"]);
        $amount = $data["amount"];
        $currency = $data["currency"];
        get_and_write($date, $currency);

        $eur = convert_any_to_eur($amount, $currency, $date);
        echo $data["date"].": ".$amount.$currency." (".$eur."EUR)";
        echo "<hr/>";
    }
}

function convert_between($from, $to, $currencies){
    $from = new DateTime($from);
    $to = new DateTime($to);
    // TODO
}

convert_posts($sample);
$date = new DateTime("2018-04-13");
// $stored = read_rates($date);
// header("Content-Type: application/json");
// echo json_encode($stored);
?>
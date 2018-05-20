<?
require_once("settings.php");

function get_fixer_rates($date, $currencies){
    global $FIXERAPI;
    if(is_array($currencies)){
        $currencies = implode($currencies, ",");
    }
    $date = $date->format("Y-m-d");
    $endpoint = "http://data.fixer.io/api/$date?access_key=$FIXERAPI&symbols=USD,GBP,$currencies";
    $rates = file_get_contents($endpoint);
    $rates = json_decode($rates, true);
    return array("EUR" => $rates["rates"]);
}

function get_fixer_deprecated($date, $currencies){
    // Deprecated API 2018-06-01 but no rate limit, good for testing til then
    if(is_array($currencies)){
        $currencies = implode($currencies, ",");
    }
    $date = $date->format("Y-m-d");
    $endpoint = "https://api.fixer.io/$date?base=EUR&symbols=$currencies";
    $rates = file_get_contents($endpoint);
    $rates = json_decode($rates, true);
    return array("EUR" => $rates["rates"]);
}

function get_currencylayer_rates($date, $currencies){
    // TODO;
}

function read_rates($date){

    global $RATESPATH;
    $fn = $RATESPATH.$date->format("Y-m-d");
    if(file_exists($fn)){
        $rates = json_decode(file_get_contents($fn), true);
    }else{
        $rates = array();
    }
    return $rates;
}

function read_rate($date, $currency, $base="EUR"){
    $rates = read_rates($date);
    if(isset($rates[$base][$currency])){
        return $rates[$base][$currency];
    }else{
        return null;
    }
}

function write_rates($date, $rates, $base="EUR"){

    global $RATESPATH;
    $datef = $date->format("Y-m-d");

    $data = array("date" => $datef);

    $fn = $RATESPATH.$datef;
    if(file_exists($fn)){
        $existing = json_decode(file_get_contents($fn), true);

        if(isset($existing[$base]) && is_array($existing[$base])){
            $updated = array_merge($existing[$base], $rates[$base]);
        }else{
            $updated = $rates[$base];
        }
    }
    $data[$base] = $updated;
    $json = json_encode($data);
    file_put_contents($fn, $json);
}

function get_and_write($date, $currency, $base="EUR"){
    $existing = read_rates($date);
    if(isset($existing[$base][$currency])){
        return $existing;
    }
    $rates = get_fixer_rates($date, $currency);
    write_rates($date, $rates);
    return $rates;
}

function convert_eur_to_any($amount, $currency, $date){
    $rate = read_rate($date, $currency);
    $any = $amount * $rate;
    return number_format($any, 2, '.', '');
}

function convert_any_to_eur($amount, $currency, $date){
    $rate = read_rate($date, $currency);
    $eur = $amount / $rate;
    return number_format($eur, 2, '.', '');
}


?>
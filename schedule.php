<?
session_start();
require_once('vendor/init.php');

$base = 'https://rhiaro.co.uk';
$uri = 'https://rhiaro.co.uk/schedule';

if(isset($_GET['key'])){
    $graph = restricted_graph_by_key($_GET['key']);
}else{
    $graph = "https://blog.rhiaro.co.uk"
}

function schedule_text($resource, $subject, $key=null){
    global $S_KEY;
    global $P_KEY;
    global $R_KEY;
    if($key == $S_KEY){

    }
    elseif($key == $P_KEY){

    }
    elseif($key == $R_KEY){
        $name = get_value($resource, 'as:name', $subject);
        $location = get_value($resource, 'as:location', $subject);
        $object = get_value($resource, 'as:object', $subject);
        if(!$name){
            if($location){
                $name = get_value($resource, 'as:name', $location);
            }elseif($object){
                $name = get_value($resource, 'as:name', $object);
            }
        }
        return '<a href="'.$subject.'">'.$name.'</a>';
    }else{
        return 'busy';
    }
}

$month = $_GET['m'];
$year = $_GET['y'];
$datestr = "$year-$month-01T00:00:00+00:00";
$date = new DateTime($datestr);
$days = $date->format('t');

$g = new EasyRdf_Graph($uri);
$g->add($uri, 'as:name', 'Schedule');
$g->addResource($uri, 'view:stylesheet', 'views/calendar.css');

$busy = "$base/schedule/busy";
$free = "$base/schedule/free";
$transit = "$base/location/transit";
$other = "$base/location/other";
$occrp = "$base/projects/occrp";
$w3c = "$base/projects/w3c";
$credi = "$base/projects/credi";

// Query for all Events and Travels from https://blog.rhiaro.co.uk/ and https://rhiaro.co.uk/schedule where startTime >= firstdayofmonth OR endTime <= lastdayofmonth

$g->add($busy, 'as:name', 'Busy');
$g->add($free, 'as:name', 'Free');
$g->add($transit, 'as:name', 'Travelling');
$g->add($other, 'as:name', 'Adventuring');
$g->add($occrp, 'as:name', 'OCCRP');
$g->add($w3c, 'as:name', 'W3C');
$g->add($credi, 'as:name', 'Credibility Indicators');

$e1 = "$base/$year/$month/".uniqid();
$g->addResource($e1, 'rdf:type', 'as:Event');
$g->add($e1, 'as:startTime', '2018-03-21T15:00:00+01:00');
$g->add($e1, 'as:endTime', '2018-04-01T10:00:00+01:00');
$g->addResource($e1, 'as:location', 'http://dbpedia.org/resource/Mariazell');
$g->add($e1, 'as:name', 'Vipassana Meditation');

$e2 = "$base/$year/$month/".uniqid();
$g->addResource($e2, 'rdf:type', 'as:Event');
$g->add($e2, 'as:location', $transit);
$g->add($e2, 'as:startTime', '2018-03-21T12:00:00+01:00');
$g->add($e2, 'as:endTime', '2018-03-21T15:00:00+01:00');

$e3 = "$base/$year/$month/".uniqid();
$g->addResource($e3, 'rdf:type', 'as:Event');
$g->add($e3, 'as:object', $occrp);
$g->add($e3, 'as:startTime', '2018-03-08T10:00:00+01:00');
$g->add($e3, 'as:endTime', '2018-03-14T18:00:00+01:00');

$resource = $g->toRdfPhp();
// echo "<pre>";
// var_dump($resource);
// echo "</pre>";

$calendar = [];
for($d=1;$d<=$days;$d++){
    $day = str_pad($d, 2, '0', STR_PAD_LEFT);
    $str = "$year-$month-{$day}T00:00:00+00:00";
    $calendar[$d]['date'] = new DateTime($str);
    foreach($resource as $uri => $data){
        if(has_type(array($uri=>$data), 'as:Event', $uri) || has_type(array($uri=>$data), 'as:Travel', $uri)){
            $start = new DateTime(get_value(array($uri=>$data), 'as:startTime', $uri));
            $end = new DateTime(get_value(array($uri=>$data), 'as:endTime', $uri));

            if($calendar[$d]['date']->format('Ymd') >= $start->format('Ymd') && $calendar[$d]['date']->format('Ymd') <= $end->format('Ymd')){
                $calendar[$d]['text'][] = schedule_text($resource, $uri, $key);
            }
            
        }
    }
}

// var_dump($calendar);

include 'views/top.php';
include 'views/nav.php';
?>
<div class="w1of1 clearfix">
    <div class="schedule-wrapper">
        <?for($d=1;$d<=$days;$d++):?>
            <div class="w1of7">
                <div class="inner" style="border: 1px solid #eee">
                    <h3><?=$d?></h3>
                    <p><?=isset($calendar[$d]['text']) ? implode(', ', $calendar[$d]['text']) : ""?></p>
                </div>
            </div>
        <?endfor?>
    </div>
</div>
<?
include 'views/end.php';
?>
<style type="text/css">
  table {
    font-family: sans-serif;
    width: 100%;
    border-spacing: 0;
    border-collapse: separate;
    table-layout: fixed;
    margin-bottom: 50px;
  }
  table thead tr th {
    background: #626E7E;
    color: #d1d5db;
    padding: 0.5em;
    overflow: hidden;
  }
  table thead tr th:first-child {
    border-radius: 3px 0 0 0;
  }
  table thead tr th:last-child {
    border-radius: 0 3px  0 0;
  }
  table thead tr th .day {
    display: block;
    font-size: 1.2em;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    margin: 0 auto 5px;
    padding: 5px;
    line-height: 1.8;
  }
  table thead tr th .day.active {
    background: #d1d5db;
    color: #626E7E;
  }
  table thead tr th .short {
    display: none;
  }
  table thead tr th i {
    vertical-align: middle;
    font-size: 2em;
  }
  table tbody tr {
    background: #d1d5db;
  }
  table tbody tr:nth-child(odd) {
    background: #c8cdd4;
  }
  table tbody tr:nth-child(4n+0) td {
    border-bottom: 1px solid #626E7E;
  }
  table tbody tr td {
    text-align: center;
    vertical-align: middle;
    border-left: 1px solid #626E7E;
    position: relative;
    height: 32px;
    cursor: pointer;
  }
  table tbody tr td:last-child {
    border-right: 1px solid #626E7E;
  }
  table tbody tr td.hour {
    font-size: 2em;
    padding: 0;
    color: #626E7E;
    background: #fff;
    border-bottom: 1px solid #626E7E;
    border-collapse: separate;
    min-width: 100px;
    cursor: default;
  }
  table tbody tr td.hour span {
    display: block;
  }
  table tbody tr td.hour.active span {
    font-weight: bold;
  }
  @media (max-width: 60em) {
    table thead tr th .long {
      display: none;
    }
    table thead tr th .short {
      display: block;
    }
    table tbody tr td.hour span {
      transform: rotate(270deg);
      -webkit-transform: rotate(270deg);
      -moz-transform: rotate(270deg);
    }
  }
  @media (max-width: 27em) {
    table thead tr th {
      font-size: 65%;
    }
    table thead tr th .day {
      display: block;
      font-size: 1.2em;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      margin: 0 auto 5px;
      padding: 5px;
    }
    table thead tr th .day.active {
      background: #d1d5db;
      color: #626E7E;
    }
    table tbody tr td.hour {
      font-size: 1.7em;
    }
    table tbody tr td.hour span {
      transform: translateY(16px) rotate(270deg);
      -webkit-transform: translateY(16px) rotate(270deg);
      -moz-transform: translateY(16px) rotate(270deg);
    }
  }
</style>
<? 
$test = array(
          array(
            "name" => "Event 1",
            "startTime" => new DateTime("2016-09-17T09:00:00-0400"),
            "endTime" => new DateTime("2016-09-17T12:30:00-0400"),
          ),
          array(
            "name" => "I like big butts",
            "startTime" => new DateTime("2016-09-14T14:00:00-0400"),
            "endTime" => new DateTime("2016-09-14T17:45:00-0400"),
          ),
          array(
            "name" => "Multi day",
            "startTime" => new DateTime("2016-09-13T09:00:00-0400"),
            "endTime" => new DateTime("2016-09-14T17:00:00-0400"),
          ),
          array(
            "name" => "Flight",
            "startTime" => new DateTime("2016-09-16T23:30:00-0400"),
            "endTime" => new DateTime("2016-09-16T02:00:00-0400"),
          )
  );

$this_week = array();
$today = new DateTime(); 
$today->setTimezone(new DateTimeZone("EST"));
if($today->format("N") == 1){ 
  $this_week[1]["date"] = new DateTime();
  $this_week[1]["today"] = true; 
}else{ $this_week[1]["date"] = new DateTime("last Monday"); }
for($day=2;$day<=7;$day++){
  $this_week[$day]["date"] = new DateTime($this_week[$day-1]["date"]->format(DATE_ATOM). " + 1 day");
  if($today->format("N") == $this_week[$day]["date"]->format("N")){
    $this_week[$day]["today"] = true;
  }
}

$hours = array_merge(range($today->format("G")-1, 23), range(1, $today->format("G")-2));

?>
<table>
  <thead>
    <tr>
      <th></th>
      <?foreach($this_week as $day):?>
      <th>
        <span class="day<?=isset($day["today"]) ? " active" : ""?>"><?=$day["date"]->format("d")?></span>
        <span class="long"><?=$day["date"]->format("l")?></span>
        <span class="short"><?=$day["date"]->format("D")?></span>
      </th>
      <?endforeach?>
    </tr>
  </thead>
  <tbody>
    <?foreach($hours as $hour):?>
      <tr>
        <td class="hour<?=$today->format("G") == $hour ? " active" : ""?>" rowspan="4" id=<?=$hour?>><span><?=$hour?>:00</span></td>
      </tr>
      <?for($i=15;$i<=45;$i+=15):?>
        <tr>
          <?foreach($this_week as $day):?>
            <td>
              <?foreach($test as $event):?>
                <?if($event["startTime"]->format("d") <= $day["date"]->format("d") && $event["endTime"]->format("d") >= $day["date"]->format("d")):?>
                  <?if($event["startTime"]->format("G") <= $hour && $event["endTime"]->format("G") >= $hour):?>
                    <?=$event["name"]?>
                  <?endif?>
                <?endif?>
              <?endforeach?>
            </td>
          <?endforeach?>
        </tr>
      <?endfor?>
    <?endforeach?>
  </tbody>
</table>
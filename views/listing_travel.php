<div id="themap" style="display:none"></div>

  <article id="tablewrapper">

    <p id="noscript">For an interactive map of all the places I've been, you need to enable javascript (sorry). Instead, here it is in table form, sorted alphabetically by place name.</p>

    <p><em>This is a work in progress, there is currently lots of data missing.</em></p>
    <p>You can also see <a href="/travel">my travel plans</a> (from which this data is derived) and <a href="/places">places</a>.</p>

  <table>
    <thead>
      <tr>
        <th class="sort" data-sort="where">Where</th>
        <th class="sort" data-sort="when">When</th>
        <th class="sort" data-sort="until">Until</th>
        <th class="sort" data-sort="for">For</th>
      </tr>
    </thead>
    <tbody class="list" id="thetable">
      <?foreach($data as $travel):?>
        <?foreach($travel["visits"] as $visit):?>
          <?
          $start = new DateTime($visit["startDate"]);
          $end = new DateTime($visit["endDate"]);
          ?>
          <tr>
            <td><?=$travel["name"]?></td>
            <td><?=$start->format("d M Y")?></td>
            <td><?=$end->format("d M Y")?></td>
            <td><?=time_diff_to_human($start, $end)?></td>
          </tr>
        <?endforeach?>
      <?endforeach?>
    </tbody>
  </table>
</article>

<script>
  document.getElementById("themap").style.display = "block";
  document.getElementById("noscript").style.display = "none";
  data = <?=json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)?>;
</script>
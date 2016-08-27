<?
function lat_lon_to_map($lat, $lon, $zoom=8){
  $x = floor((($lon + 180) / 360) * pow(2, $zoom));
  $y = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));
  //$map = "http://b.tile.openstreetmap.org/$zoom/$x/$y.png";
  $map = "http://a.basemaps.cartocdn.com/light_all/$zoom/$x/$y.png";
  return $map;
}

function next_tile_x($tile){
  $url = explode("/", $tile);
  $xpos = count($url) - 2;
  $url[$xpos] = $url[$xpos] + 1;
  return implode("/", $url);
}
function prev_tile_x($tile){
  $url = explode("/", $tile);
  $xpos = count($url) - 2;
  $url[$xpos] = $url[$xpos] - 1;
  return implode("/", $url);
}
?>
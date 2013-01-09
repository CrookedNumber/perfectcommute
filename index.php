$url = "http://developer.mbta.com/lib/RTCR/RailLine_8.json";

date_default_timezone_set('America/New_York');

$json = file_get_contents($url);
$obj = json_decode($json);

//print "<pre>" . print_r($obj, 1) . "</pre>";

$trips = array();

foreach($obj->Messages as $messages) {
  $tripstop = new stdClass();
  foreach ($messages as $datum) {
    $tripstop->{$datum->Key} = $datum->Value;
  }
  if ($tripstop->Stop == 'West Newton') {
    $s = "<p>Train: $tripstop->Trip</p>";	
    $s .= "<p>Stop: $tripstop->Stop</p>";
    $s .= "<p>Scheduled: " . date('h:i a', $tripstop->Scheduled) . "</p>";
    $lateness = ($tripstop->Lateness) ? $tripstop->Lateness/60 : 0;
    $emph = ($tripstop->Lateness) ? ' style="font-weight: bold;"' : '' ; 
    $expected = "Expected: " . date('h:i a', $tripstop->Scheduled+$tripstop->Lateness);
    $s .= "<p" . $emph . ">" . $expected . "</p>";
    $s .= "<p>Speed: $tripstop->Speed</p>";
    $lateness = ($tripstop->Lateness) ? $tripstop->Lateness/60 : 0;
    $s .= "<p>Lateness: $lateness</p>";
    //$s .= "<p>-------</p>";

    $trips[$tripstop->Trip][] = $s;
  }
}

foreach ($trips as $k=>$v) {
  //print $k . '<br>';
  foreach ($v as $vv) {
    print $vv;
  }
  print "<p>--------------------------</p>";
}

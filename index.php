<?php
define('URL', 'http://developer.mbta.com/lib/RTCR/RailLine_8.json');
define('TIMEZONE', 'America/New_York');
define('DESIRED_STOP', 'West Newton');
define('AM_DIRECTION', 'inbound');

date_default_timezone_set(TIMEZONE);
$json = file_get_contents(URL);
$obj = json_decode($json);

$trips = array();

foreach($obj->Messages as $messages) {
  $tripstop = new stdClass();
  foreach ($messages as $datum) {
    $tripstop->{$datum->Key} = $datum->Value;
  }
  $direction = ($tripstop->Trip % 2) ? 'outbound' : 'inbound';
  $merid = date('a');
  
  if ($tripstop->Stop == DESIRED_STOP && (($merid == 'am' && $direction == AM_DIRECTION) || ($merid = 'pm' && $direction != AM_DIRECTION))) {
    $s  = "<p>Train: $tripstop->Trip</p>";	
    $s .= "<p>Stop: $tripstop->Stop</p>";    
    $s .= "<p>Scheduled: " . date('h:i a', $tripstop->Scheduled) . "</p>";
    $lateness = ($tripstop->Lateness) ? $tripstop->Lateness/60 : 0;
    $emph = ($tripstop->Lateness) ? ' style="font-weight: bold;"' : '' ; 
    $expected = "Expected: " . date('h:i a', $tripstop->Scheduled+$tripstop->Lateness);
    $s .= "<p" . $emph . ">" . $expected . "</p>";
    $s .= "<p>Speed: $tripstop->Speed</p>";
    $lateness = ($tripstop->Lateness) ? $tripstop->Lateness/60 : 0;
    $s .= "<p>Lateness: $lateness</p>";
    $trips[$tripstop->Trip][] = $s;
  }
}

foreach ($trips as $k=>$v) {
  foreach ($v as $vv) {
    print $vv;
  }
  print "<p>--------------------------</p>";
}

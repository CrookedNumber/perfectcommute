<?php
// These values can all be changed and the code forked
// TO accomodate your particular commute
// @TODO: provide a UI and cookied info
// @TODO: make the line configurable (hardcoded to Framingham[8])
define('URL', 'http://developer.mbta.com/lib/RTCR/RailLine_8.json');
define('TIMEZONE', 'America/New_York');
define('AM_DEPARTURE', 'West Newton');
define('PM_DEPARTURE', 'South Station');
define('AM_DIRECTION', 'inbound');

date_default_timezone_set(TIMEZONE);
$json = file_get_contents(URL);
$obj = json_decode($json);

$trips = array();

foreach($obj->Messages as $messages) {
  $tripstop = new stdClass();
  // Turn messages into more commonsense objects
  foreach ($messages as $datum) {
    $tripstop->{$datum->Key} = $datum->Value;
  }
  
  // Trip comes as string, prefixed with a 'P'
  // (Though the cast is probably unnecessary)
  $trip_as_int = (int)substr($tripstop->Trip, 1);
  
  // Outbound trip numbers are odd; inbound are even.
  $direction = ($trip_as_int % 2) ? 'outbound' : 'inbound';
  
  // Is it AM or PM?
  $merid = date('a');
  
  $trip_to = ($tripstop->Stop == AM_DEPARTURE && $merid == 'am' && $direction == AM_DIRECTION);
  $trip_from = ($tripstop->Stop == PM_DEPARTURE && $merid == 'pm' && $direction != AM_DIRECTION);

  // Filter out any irrelevant stops
  // i.e., trains headed counter to your commute
  if ($trip_to || $trip_from) {
    $trip_info  = '<p><strong>'. date('g:i', $tripstop->Scheduled) . '</strong> will leave ';	
    $trip_info .= "$tripstop->Stop at ";    
    $lateness = ($tripstop->Lateness) ? $tripstop->Lateness/60 : 0;
    $label = ($lateness > 1) ? 'minutes' : 'minute';
    $expected = date('g:ia', $tripstop->Scheduled + $tripstop->Lateness);
    $trip_info .= $expected;
    if ($lateness) {
      $trip_info .= ' <strong>[' . $lateness. ' ' . $label . ' late]</strong>';    
    }
    $trip_info .= " Speed: $tripstop->Speed</p>";
    $trips[$tripstop->Trip][] = $trip_info;
  }
}
?>
<html>
<head>
</head>
<body>
<?php
foreach ($trips as $trip) {
  foreach ($trip as $trip_info) {
    print $trip_info;
  }
  print "<hr>";
}
?>
</body>
</html>
<?php
// These values can all be changed and the code forked
// TO accomodate your particular commute
// @TODO: provide a UI and cookied info
// @TODO: make the line configurable (hardcoded to Framingham[8])
// @TODO: cache API results
// @TODO: sort relevant trips by time
define('URL', 'http://developer.mbta.com/lib/RTCR/RailLine_8.json');
define('TIMEZONE', 'America/New_York');
define('AM_DEPARTURE', 'West Newton');
define('PM_DEPARTURE', 'South Station');
define('AM_DIRECTION', 'inbound');

date_default_timezone_set(TIMEZONE);
$json = file_get_contents(URL);
$obj = json_decode($json);

$trips = array();

$relevant_trips =array();

foreach($obj->Messages as $tripstop) {
  
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
    $relevant_trips[] = $tripstop;
  }
}

$i = 1;
foreach($relevant_trips as $tripstop) {
    $trip_info = '<div class="page" data-role="page" id="page-' . $i . '"><div data-role="content">';
    $trip_info  .= '<p class="trip">'. date('g:i', $tripstop->Scheduled) . ' will leave ';	
    $trip_info .= "$tripstop->Stop in "; 
    $lateness = ($tripstop->Lateness) ? $tripstop->Lateness/60 : 0;
    $label = ($lateness > 1) ? 'minutes' : 'minute';
    //$expected = date('g:ia', $tripstop->Scheduled + $tripstop->Lateness);
    $time = floor(($tripstop->Scheduled + $tripstop->Lateness - time()) / 60);
    $unit = ($time > 1) ? 'minutes' : 'miuntes';
    $trip_info .= '<span style="font-size: 100px; font-weight: bold;">' . $time . '</span> ' . $unit;
    if ($lateness) {
      $trip_info .= ' <strong>[' . $lateness. ' ' . $label . ' late]</strong>';    
    }
    if ($tripstop->Speed) {
      $trip_info .= " Speed: $tripstop->Speed";
    }
    $trip_info .= '</p>';
    
    $prev = $i - 1;
    $next = $i + 1;
    $first = $prev <= 0;
    $last = $next >= count($relevant_trips);
    if (!$first) {
      $trip_info .= '<a class="prev" href="#page-' . $prev .'">PREV</a>';
    }
    if (!$first && !$last) {
      $trip_info .= ' | ';   
    }
    if (!$last) {
      $trip_info .= '<a class="next" href="#page-' . $next .'">NEXT</a>';
    }
    $trip_info .= '</div></div>';
    $trips[$tripstop->Trip][] = $trip_info;
    $i++;
}
?>
<html>
<head>
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.0/jquery.mobile-1.3.0.min.css" />
<script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
<script src="http://code.jquery.com/mobile/1.3.0/jquery.mobile-1.3.0.min.js"></script>
<script>
  $(document).ready(function() {
    $(".page").swiperight(function() {
      var prev = $(this).find('.prev').attr('href');
      if (prev) {
        $.mobile.changePage(prev, {transition: "slide", reverse: "true"});
      }
    });
    $(".page").swipeleft(function() {
      var next = $(this).find('.next').attr('href');
      if (next) {
        $.mobile.changePage(next, {transition: "slide"});
      }
    });
  });
</script>
</head>
<body>
<?php
foreach ($trips as $trip) {
  foreach ($trip as $trip_info) {
    print $trip_info;
  }
}
?>
</body>
</html>
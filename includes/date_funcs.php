<?php

//Function for retrieving a date from a time stamp in human readable format.
function date_h($time)
{
$date_format = "l, F d, Y  h:i:s A T";
$time = intval($time);
if (!$time)
return "";
return date($date_format, $time);
}

?>
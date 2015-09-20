<?php
//Convert seconds to a human readable format.
function secs_to_h($secs)
{
$units = array(
"week" => 7*24*3600,
"day" => 24*3600,
"hour" => 3600,
"minute" => 60,
"second" => 1);
// specifically handle zero, or a value less than 1 if it's a floating point number
//Trying out handling anything less than a minute.
if ($secs < 60)
return "$secs seconds";
$s = "";
foreach ( $units as $name => $divisor )
{
if ($name == "second")
{
$quot = round($secs / $divisor, 2);
}
else
{
$quot = intval($secs / $divisor);
}
if ($quot)
{
$s .= "$quot $name";
$s .= (abs($quot) > 1 ? "s" : "") . ", ";
$secs -= $quot * $divisor;
}
}
return substr($s, 0, -2);
}

//Convert time formats like MM:SS, HH:MM:SS, or DD:HH:MM:SS to seconds only.
function time_to_secs($time)
{
$timeArr = array_reverse(split(":", $time));
$seconds = 0;
$vals = Array(1, 60, 3600, 86400);
foreach($timeArr as $key => $value)
{
if(!isset($vals[$key]))
break;
$seconds += $value * $vals[$key];
}
return $seconds;
}
?>
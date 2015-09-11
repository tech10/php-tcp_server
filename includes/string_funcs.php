<?php

//Various functions for strings.

//This function will return true if a string contains the entered string, the string representation of a number, or any strings in an array.
function string_find($mainStr, $chars)
{
if (!is_array($chars))
return strpos($mainStr, $chars);
foreach ($chars as $string)
{
$result = strpos($mainStr, $string);
if ($result !== FALSE)
return $result;
}
return FALSE;
}
?>
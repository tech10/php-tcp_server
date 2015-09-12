<?php
//Functions for splitting parameters.

function params_split($string, $delimiter = " ", $wordchar = '"')
{
$result = Array();
$res_index = 0;
$word = 0;
foreach (explode($delimiter, $string) as $data)
{
if (strpos($data, $wordchar) === 0 && strrpos($data, $wordchar) !== strlen($data) - 1)
{
if (!$word && $res_index && isset($result[$res_index]))
$res_index++;
$word++;
if (!isset($result[$res_index]))
$result[$res_index] = "";
$result[$res_index] .= substr($data, 1) . $delimiter;
continue;
}
if (strrpos($data, $wordchar) === strlen($data) - 1)
{
$word--;
$result[$res_index] .= substr($data, 0, -1);
$res_index++;
continue;
}
if (!$word)
{
if (isset($result[$res_index]))
$res_index++;
$result[$res_index] = $data;
continue;
}
else
{
$result[$res_index] .= $data . $delimiter;
continue;
}
}
return $result;
}
?>
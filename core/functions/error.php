<?php
global $data_dir;
set_error_handler("error_handler");

function error_handler($errno, $errstr, $errfile, $errline)
{
if (!error_reporting())
return true;
global $data_dir;
$err_file = $data_dir . "server.err";
$err_msg = "Error number: $errno" . NL . "Error string: $errstr" . NL . "File the error took place in: $errfile" . NL . "Line number in the file: $errline" . NL;
file_put_contents($err_file, $err_msg, FILE_APPEND);
return true;
}
?>

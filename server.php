#!/usr/bin/php
<?php
$load_start = microtime(true);
declare(ticks=1);
$phpversion_req = '5.4';
if (version_compare(phpversion(), $phpversion_req, '<'))
die('You are currently running php version ' . phpversion() . ', and this script requires at least php version ' . $phpversion_req . "\r\n");
$current_dir = dirname(__FILE__);
chdir($current_dir);
$user = get_current_user();
$pid = getmypid();
$bindaddr = '[::]';
$port = 6000;
flush();
set_time_limit(0);

//Include needed files.
$include_dir = $current_dir . '/includes/';
$dir = opendir($include_dir);
if ($dir)
{
while (false !== ($file = readdir($dir)))
{
$ext = substr($file, -3);
if (!is_dir($include_dir . $file) && $file != "." && $file != ".." && $ext == "php")
require_once($include_dir . $file);
}
closedir($dir);
}

$server_directory = $current_dir;
$server_filename = __FILE__;
server_log(server_name() . " version " . server_version() . ".\r\n", TRUE);
server_log("Current working directory: " . getcwd() . "\r\n");
server_log("Running as user: $user.\r\n");
server_log("Process ID: $pid.\r\n");

$result = server_start($bindaddr, $port);
if ($result === FALSE)
die("Error: The server failed to start.\r\n");
server_log("Server started successfully binding to the address $bindaddr and the port $port.", TRUE);
server_log("Script loaded in " . round(microtime(true) - $load_start, 3) . " seconds.\r\n");
$loop_time_highest = 0;
$loops = 0;
while (true)
{
$loop_time_begin = microtime(true);
$loops++;
//Do all the server events.
server_do_events();
//Sleep so we're not hogging CPU like we can't get enough.
usleep(5000);
$loop_time = microtime(true) - $loop_time_begin;
if ($loop_time > $loop_time_highest)
{
server_log("Loop iterations: $loops\r\nCurrent highest: $loop_time_highest seconds.\r\nNew highest time: $loop_time seconds.\r\nDifference: " . ($loop_time - $loop_time_highest) . " seconds.\r\n", TRUE);
$loop_time_highest = $loop_time;
}
}
server_stop();
?>

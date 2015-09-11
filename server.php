#!/usr/bin/php
<?php
$load_start = microtime(true);
declare(ticks=1);
$phpversion_req = '5.4';
if (version_compare(phpversion(), $phpversion_req, '<'))
die('You are currently running php version ' . phpversion() . ', and this script requires at least php version ' . $phpversion_req . "\r\n");
define('DS', DIRECTORY_SEPARATOR);
$current_dir = __DIR__ . DS;
chdir($current_dir);
$user = get_current_user();
$pid = getmypid();
$bindaddr = '[::]';
$port = 6000;
set_time_limit(0);

//Include needed files.
foreach (glob($current_dir . "includes" . DS . "*.php") as $inc_file)
{
require_once($inc_file);
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
while (true)
{
//Do all the server events.
server_do_events();
//Sleep so we're not hogging CPU like we can't get enough.
usleep(5000);
}
server_stop();
?>

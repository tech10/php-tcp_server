#!/usr/bin/php
<?php
$load_start = microtime(true);
declare(ticks=1);
define('DS', DIRECTORY_SEPARATOR);
define('NL', PHP_EOL);
$phpversion_req = '5.4';
if (version_compare(phpversion(), $phpversion_req, '<'))
die('You are currently running php version ' . phpversion() . ', and this script requires at least php version ' . $phpversion_req . NL);
$current_dir = __DIR__ . DS;
chdir(__DIR__);

//Include needed files.
require_once(__DIR__ . DS . "core" . DS . "bootstrap.php");

//Define some useful variables.
$user = get_current_user();
$pid = getmypid();
$bindaddr = '[::]';
$port = 6000;

$server_directory = $current_dir;
$server_filename = __FILE__;
server_log(server_name() . " version " . server_version() . ".", TRUE);
server_log("Current working directory: " . getcwd());
server_log("Running as user: $user.");
server_log("Process ID: $pid.");

$result = server_start($bindaddr, $port);
if ($result === FALSE)
die("Error: The server failed to start." . NL);
server_log("Server started successfully binding to the address $bindaddr and the port $port.", TRUE);
server_log("Script loaded in " . round(microtime(true) - $load_start, 3) . " seconds.");
while (true)
{
//Do all the server events.
server_do_events();
//Sleep so we're not hogging CPU like we can't get enough.
usleep(5000);
}
server_stop();
?>
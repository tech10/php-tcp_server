<?php
global $log_dir, $log_file, $server, $server_start_time;
$log_dir = getcwd() . DS . "data" . DS;
$log_file = "server.log";
global $server_clients, $server_clients_max;
$server_clients_max = 100;

//Do not modify these variables.
$server_clients = 0;

//Various server functions

function server_start($bindaddr, $port)
{
global $server, $server_start_time;
$server = new tcp_server($bindaddr, $port, "process_connect", "process_disconnect", "process_recv", 8192, "time_checks", 1);
$server_start_time = microtime(true);
return $server->start();
}

function server_started()
{
global $server_start_time;
return $server_start_time;
}

function server_uptime()
{
if (server_started())
return round(microtime(true) - server_started(), 1);
return 0;
}

function server_stop()
{
global $server, $server_start_time;
$server = FALSE;
$server_start_time = FALSE;
return TRUE;
}

function server_do_events()
{
global $server;
return $server->do_events();
}

function server_log($text, $date = FALSE)
{
if (!$text)
return;
global $log_dir, $log_file;
$text = trim($text) . NL;
if ($date)
$text = "<" . date_h(time()) . ">  " . $text;
$file = $log_dir . $log_file;
if (!file_exists($log_dir))
mkdir($log_dir, 0750, TRUE);
if (!file_exists($file))
touch($file);
file_put_contents($file, $text, FILE_APPEND);
echo $text;
return TRUE;
}

function server_send_all($text, $socket = FALSE, $log = FALSE, $date = FALSE)
{
global $server;
if ($log)
server_log($text, $date);
return $server->send_all($text, $socket);
}

function server_send_all_authorized($text, $socket = FALSE, $log = FALSE, $date = FALSE)
{
global $server;
if ($log)
server_log($text, $date);
$sent_clients = 0;
foreach ($server->sockets() as $client)
{
if ($client === $socket || !$server->socket_data_get($client, "authorized"))
continue;
$server->send($client, $text);
$sent_clients++;
}
return $sent_clients;
}

function server_send($socket, $text, $log = FALSE, $date = FALSE)
{
global $server;
if ($log)
server_log($text, $date);
return $server->send($socket, $text);
}

function server_restart($msg = "Server restarting. Localy initiated.")
{
global $pid, $server, $server_filename, $server_directory;
$server->disconnect_all($msg);
server_log($msg, TRUE);
server_stop();
sleep(1);
$cmd = PHP_BINARY . " $server_filename";
$output = "2>&1 > $server_directory/output.txt &";
if (substr(php_uname(), 0, 7) == "Windows")
{
pclose(popen("start /B " . $cmd, "r"));
}
else
{
//exec("$cmd $output");
pclose(popen("kill -9 $pid; nohup $cmd $output", "r"));
} 
exit;
}

function server_clients_most()
{
global $server_clients;
static $server_clients_most = 0;
if ($server_clients > $server_clients_most)
$server_clients_most = $server_clients;
return $servver_clients_most;
}
function server_clients_add()
{
global $server_clients, $server_clients_max;
if ($server_clients == $server_clients_max)
return FALSE;
$server_clients++;
return TRUE;
}
function server_clients_remove()
{
global $server_clients;
$server_clients--;
return $server_clients;
}
function server_clients()
{
global $server_clients;
return $server_clients;
}

function server_name()
{
return "Generic chat server";
}
function server_version()
{
return "0.1 Alpha1";
}
?>
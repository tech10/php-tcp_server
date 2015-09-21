<?php

//Custom functions to process information sent from the class.
function process_connect($server, $socket)
{
//Do any pre-connecting processing here, say, checking for a bann IP address.

$id = $server->socket_uid_get($socket);
$ip = $server->get_ip($socket);
$connected_time = $server->socket_time_connected($socket);
server_log("Client $id connected from $ip.", TRUE);
$result = server_send($socket, "Welcome to the " . server_name() . ", version " . server_version() . ".");
user_auth($server, $socket);
return TRUE;
}

function process_recv($server, $socket, $data)
{
$id = $server->socket_uid_get($socket);
$nickname = user_nickname_get($server, $socket);
$result = user_func_call($server, $socket, $data);
if ($result !== NULL)
return $result;
$result = process_cmd($server, $socket, $data);
if ($result !== NULL)
return $result;

if ($data)
{
//Send this to authorized clients, accept the sending client, and log it.
server_send_all_authorized("$nickname says: $data", $socket, TRUE);
//Send this to the sending client.
server_send($socket, "You say: $data");
}
return TRUE;
}

function process_disconnect($server, $socket)
{
$time = microtime(true);
server_send($socket, "Disconnected.");
$id = $server->socket_uid_get($socket);
$nickname = user_nickname_get($server, $socket);
if (!$server->socket_data_get($socket, "authorized"))
{
if (!$nickname)
server_log("Client $id has disconnected, and was connected for " . secs_to_h(round($time - $server->socket_time_connected($socket), 1)) . ".", TRUE);
return;
}
server_send_all_authorized("$nickname has disconnected, and was connected for " . secs_to_h(round($time - $server->socket_time_connected($socket), 1)) . ".", $socket, TRUE, TRUE);
server_clients_remove();
}
?>

<?php

//Functions to set, clear, and call a user function for a socket.

function user_func_set($server, $socket, $function, $params = Array())
{
$server->socket_data_set($socket, "user_func", $function);
$server->socket_data_set($socket, "user_func_params", $params);
return TRUE;
}
function user_func_clear($server, $socket)
{
$server->socket_data_clear($socket, "user_func");
$server->socket_data_clear($socket, "user_func_params");
return TRUE;
}

function user_func_call($server, $socket, $data)
{
$function = $server->socket_data_get($socket, "user_func");
$params = $server->socket_data_get($socket, "user_func_params");
if (!$function)
return;
$result = call_user_func_array($function, array_merge(Array($server, $socket, $data), $params));
if ($result === NULL)
{
user_func_clear($server, $socket);
return;
}
return $result;
}
?>
<?php
//Functions to authorize users to the server.

function user_auth($server, $socket)
{
server_send($socket, "Please enter your nickname.");
user_func_set($server, $socket, "user_auth_nick");
return true;
}
function user_auth_nick($server, $socket, $data)
{
$result = user_nickname_validate($server, $socket, $data);
if (!$result)
{
//Insert account validation here, for now return true so we don't move on to confirming.
return TRUE;
}
//Set the temporary nickname.
$server->socket_data_set($socket, "tmpnick", $data);
//Call the confirm function.
user_auth_nick_confirm($server, $socket);
return true;
}

function user_auth_nick_confirm($server, $socket, $data = NULL)
{
if ($data === "")
return TRUE;
$nickname = $server->socket_data_get($socket, "tmpnick");
if ($data === NULL)
{
server_send($socket, "Do you want to log in with the nickname $nickname?\r\nEnter yes or no.");
user_func_set($server, $socket, "user_auth_nick_confirm");
}
else if ($data == "y" || $data == "yes")
{
if (!user_nickname_validate($server, $socket, $nickname))
{
user_func_set($server, $socket, "user_auth_nick");
return TRUE;
}
user_nickname_set($server, $socket, $nickname);
$server->socket_data_clear($socket, "tmpnick");
user_auth_success($server, $socket);
}
else if ($data == "n" || $data == "no")
{
$server->socket_data_clear($socket, "tmpnick");
user_auth($server, $socket);
}
else
{
server_send($socket, "$data is an invalid entry. Please enter y or yes, or n or no.");
}
return TRUE;
}

function user_auth_success($server, $socket)
{
static $ids = 0;
$ids++;
$id = $server->socket_uid_get($socket);
if ($id !== $ids)
{
$result = $server->socket_uid_set($socket, $ids);
server_log("Client $id is now client $ids.", TRUE);
if (is_resource($result))
server_log("Client $ids is now client $id.", TRUE);
}
$nickname = user_nickname_get($server, $socket);
$server->socket_data_set($socket, "authorized", true);
user_func_clear($server, $socket);
server_clients_add();
server_log("Client $ids has authorized with the nickname $nickname.", TRUE);
server_send($socket, "Authorization successful.");

//Insert some other validation here, automatically joining any possible channels and such.

server_send($socket, "You may begin chatting at any time.");
server_send_all_authorized("$nickname has connected.", $socket);
}
?>
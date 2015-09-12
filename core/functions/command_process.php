<?php

//Commands.

function process_cmd($server, $socket, $data)
{
//Process the data based on a command, which will begin with a slash character.
if (strpos($data, "/") !== 0)
return;
$spacepos = strpos($data, " ");
if ($spacepos)
{
$cmd = str_replace(Array("/", " "), "", strtolower(substr($data, 0, $spacepos)));
}
else
{
$cmd = str_replace(Array("/"), "", strtolower($data));
}
if (!$cmd)
return;
$param = FALSE;
if ($spacepos)
$param = substr($data, $spacepos + 1);
$result = @call_user_func("cmd_$cmd", $server, $socket, $param);
if ($result === NULL)
{
server_send($socket, "Unknown command: $cmd\r\nTry /help for more assistance.");
return TRUE;
}
return $result;
}

command_info_add("quit", "Disconnects you from the server.", "/quit\r\nDisconnects you without a reason.\r\n\r\n/quit I'll be back later.\r\nDisconnects you and tells everyone you will be back later.");
function cmd_quit($server, $socket, $param)
{
$time = microtime(true);
if ($param)
{
$server->socket_data_clear($socket, "authorized");
$nickname = user_nickname_get($server, $socket);
server_send_all_authorized("$nickname has disconnected, and was connected for " . secs_to_h(round($time - $server->socket_time_connected($socket), 1)) . ".\r\nReason: $param", $socket, TRUE, TRUE);
server_send($socket, "Disconnection reason sent.");
server_clients_remove();
}
return FALSE;
}

command_info_add("help",
"Gives you a list of commands, their descriptions, and examples of each command if provided.\r\nIf you enter a command, or list of commands seperated by a space, you will receive help on the commands you entered in the order you entered them, provided they exist.",
"/help\r\nGives you the full list of commands.\r\n\r\n/help who nick\r\nWill give you help for the commands who and nick in that order.");
function cmd_help($server, $socket, $param)
{
if (!$param)
{
$msg = command_info_all($server, $socket);
}
else
{
$msg = "";
$restricted = $server->socket_data_get($socket, "restricted_commands");
foreach (explode(" ", $param) as $cmd)
{
$cmd_info = command_info($cmd);
$cmd_info_admin = command_info_admin($cmd);
if (!$server->socket_data_get($socket, "admin"))
$cmd_info_admin = FALSE;
if (!$cmd_info && !$cmd_info_admin)
{
$msg .= "Unknown command: $cmd\r\n";
continue;
}
else if ($cmd_info_admin)
{
if (isset($restricted[$cmd]) && $restricted[$cmd])
{
$msg .= "Unknown command: $cmd\r\n";
continue;
}
else
{
$msg .= $cmd_info_admin;
continue;
}
}
else if ($cmd_info)
{
$msg .= $cmd_info;
continue;
}
}
}
server_send($socket, $msg);
return 1;
}

command_info_add("me", "Allows you to enter emoticon text.", "/me looks around.\r\nReturns: Billy looks around.");
function cmd_me($server, $socket, $param)
{
if (!$param)
return server_send($socket, command_info("me"));
$nickname = user_nickname_get($server, $socket);
server_send_all_authorized("$nickname $param");
return 1;
}

command_info_add("who", "Allows you to see who is currently connected to the server other than yourself.", "/who\r\nGives you the full list of people, other than yourself, who are connected.\r\n\r\n/who B\r\nGives you a list of people that are connected with B in their nicknames.");
function cmd_who($server, $socket, $param)
{
$msg = "";
$clients = 0;
if (!$param)
{
$sockets = $server->sockets();
}
else
{
$sockets = user_nickname_find_all($server, $param);
}
foreach ($sockets as $client)
{
if (!$server->socket_data_get($client, "authorized") || $client === $socket)
continue;
$nickname = user_nickname_get($server, $client);
//Insert status, and weather or not the client is in a channel here.
$idle = secs_to_h(round($server->socket_time_idle($client), 1));
$msg .= "$nickname ";
$msg .= "(Idle time: $idle)\r\n";
$clients++;
}
if (!$msg)
{
if (!$param)
{
$msg = "You are the only person connected.";
}
else
{
if (!count($sockets))
{
$msg = "No one is connected with the nickname $param, or with $param in their nickname.";
}
else
{
$msg = "You are connected, of course.";
}
}
}
else
{
if (!$param)
{
$before_msg = "There ";
if ($clients === 1)
{
$before_msg .= "is $clients person ";
}
else
{
$before_msg .= "are $clients people ";
}
$before_msg .= "connected other than yourself.\r\n";
$msg = $before_msg . $msg;
}
else
{
if ($clients > 1)
$msg = "There are $clients people connected with $param in their nickname.\r\n$msg";
}
}
return server_send($socket, $msg);
}

command_info_add("nick", "Changes your nickname to another value.", "/nick Matt\r\nChanges your nickname to Matt.\r\n\r\n/nick Jason Daru\r\nChanges your nickname to Jason Daru.");
function cmd_nick($server, $socket, $param)
{
if (!$param)
return server_send($socket, command_info("nick"));
$valid = user_nickname_validate($server, $socket, $param);
if (!$valid)
return 1;
$nickname = user_nickname_get($server, $socket);
user_nickname_set($server, $socket, $param);
server_send($socket, "Your nickname has been changed to $param.");
server_send_all_authorized("$nickname is now known as $param.", $socket, TRUE);
return 1;
}

command_info_add("msg",
"Allows you to send a private message to a user or multiple users.",
"/msg Bill Hi there.\r\nWill send the message \"Hi there.\" to Bill.\r\n\r\n/msg Bill|Joe Hey!\r\nWill send the message \"Hey!\" to Bill and Joe.\r\n\r\n/msg \"James B|Lacey F\" Hello.\r\nWill send the message \"Hello.\" to the users James B and Lacey F.\r\n\r\nAliases:\r\nprivate, privmsg, tell");

function cmd_msg($server, $socket, $param)
{
if (!$param)
return server_send($socket, command_info("msg"));
$params = params_split($param);
if (count($params) === 1)
{
return server_send($socket, "The message is missing.\r\n\r\n" . command_info("msg"));
}
$users = params_split($params[0], "|");
unset($params[0]);
$msg = trim(implode(" ", $params));
$msg_sent = "";
$clients_sent = 0;
$nickname = user_nickname_get($server, $socket);
$sockets_sent = Array();
foreach ($users as $user)
{
$user = trim($user, '"');
$client = $server->socket_uid_find($user);
if ($client)
$user = user_nickname_get($server, $client);
$client = user_nickname_find($server, $user);
if ($client && $client !== $socket && array_search($client, $sockets_sent) === FALSE)
{
if ($server->socket_data_get($client, "authorized"))
{
$result = server_send($client, "Private message from $nickname: $msg");
$client_nick = user_nickname_get($server, $client);
$msg_sent .= "$client_nick\r\n";
$clients_sent++;
$sockets_sent[] = $client;
}
continue;
}
$clients = user_nickname_find_all($server, $user);
foreach ($clients as $client)
{
if ($client === $socket || array_search($client, $sockets_sent) !== FALSE)
continue;
$result = server_send($client, "Private message from $nickname: $msg");
$client_nick = user_nickname_get($server, $client);
$msg_sent .= "$client_nick\r\n";
$clients_sent++;
$sockets_sent[] = $client;
}
}
$msg_sent = trim($msg_sent);
if ($clients_sent === 1)
{
$msg_sent = "Your private message has been sent to $msg_sent.";
}
else if ($clients_sent > 1)
{
$msg_sent = "Your private message has been sent to the following users:\r\n$msg_sent";
}
else
{
if ($client === $socket)
{
$msg_sent = "You can't send a private message to yourself, so if you're really that lonely, connect with another computer.";
}
else
{
$msg_sent = "Your private message couldn't be sent to the nickname or nicknames you entered. They aren't connected. Please use the /who command for more information on who's connected.";
}
}
return server_send($socket, $msg_sent);
}

command_info_add("private", "An alias for /msg. Check /help msg for more information.");
function cmd_private($server, $socket, $param)
{
return cmd_msg($server, $socket, $param);
}

command_info_add("privmsg", "An alias for /msg. Check /help msg for more information.");
function cmd_privmsg($server, $socket, $param)
{
return cmd_msg($server, $socket, $param);
}

command_info_add("tell", "An alias for /msg. Check /help msg for more information.");
function cmd_tell($server, $socket, $param)
{
return cmd_msg($server, $socket, $param);
}

command_info_add("uptime", "Allows you to get yours or another users uptime.", "/uptime\r\nGets your uptime.\r\n\r\n/uptime bill\r\nGets bill's uptime.");
function cmd_uptime($server, $socket, $param)
{
$time = microtime(true);
$uptime_socket = FALSE;
if (!$param)
$uptime_socket = $socket;
$result = user_nickname_find($server, $param);
if ($result)
$uptime_socket = $result;
if ($uptime_socket === $socket && !$param)
return server_send($socket, "You have been connected for " . secs_to_h(round($time - $server->socket_time_connected($uptime_socket), 1)) . ".");
if ($uptime_socket === $socket && $result && $param)
return server_send($socket, "You have been connected for " . secs_to_h(round($time - $server->socket_time_connected($uptime_socket), 1)) . ".");
if ($uptime_socket && $result)
return server_send($socket, user_nickname_get($server, $uptime_socket) . " has been connected for " . secs_to_h(round($time - $server->socket_time_connected($uptime_socket), 1)) . ", and has been idle for " . secs_to_h(round($server->socket_time_idle($uptime_socket), 1)) . ".");
$uptime_sockets = user_nickname_find_all($server, $param);
foreach ($uptime_sockets as $uptime_socket)
{
if ($uptime_socket !== $socket)
server_send($socket, user_nickname_get($server, $uptime_socket) . " has been connected for " . secs_to_h(round($time - $server->socket_time_connected($uptime_socket), 1)) . ", and has been idle for " . secs_to_h(round($server->socket_time_idle($uptime_socket), 1)) . ".");
else
server_send($socket, "You have been connected for " . secs_to_h(round($time - $server->socket_time_connected($uptime_socket), 1)) . ".");
}
if (!count($uptime_sockets))
server_send($socket, "$param is not connected. Check the /who command for more information on who's connected.");
return 1;
}

command_info_add("date", "Will return the local server date and time.", "/date\r\n\r\nAliases: time\r\n");
function cmd_date($server, $socket, $param)
{
return server_send($socket, "Local server date: " . date_h(time()));
}

command_info_add("time", "An alias of /date. Check /help date for more information.");
function cmd_time($server, $socket, $param)
{
return cmd_date($server, $socket, $param);
}

command_info_add("info", "Retrieves server information.", "/info\r\nReturns server information.");

function cmd_info($server, $socket, $param)
{
$time = microtime(true);
$msg = server_name();
$msg .= ", version " . server_version() . ".\r\n";
$msg .= "PHP version: " . phpversion() . "\r\n";
$msg .= "Server uptime: " . secs_to_h(round(server_uptime(), 1)) . ".\r\n";
$clients = server_clients();
$msg .= ($clients > 1 ? "There are $clients people" : "You are the only person") . " currently connected.\r\n";
$msg .= "Local server date: ";
$msg .= date("l, F d, Y", $time);
$msg .= ".\r\nLocal server time: ";
$msg .= date("h:i:s A T", $time);
$msg .= "\r\n";

return server_send($socket, $msg);
}

command_info_add("echo", "Will echo back any data you send to it as a parameter.", "/echo My test.\r\nWill echo back \"My test.\" to you, excluding the quotes, of course.");
function cmd_echo($server, $socket, $param)
{
if ($param)
return server_send($socket, $param);
return server_send($socket, command_info("echo"));
}

command_info_add("clear", "Will clear the screen of any data.", "/clear\r\nClears the screen.");
function cmd_clear($server, $socket, $param)
{
$clear_char = chr(27) . "[2J";
return server_send($socket, $clear_char);
}
?>
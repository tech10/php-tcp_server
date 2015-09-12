<?php
//Some variables.

//Invalid characters or strings that a nickname or username cannot contain.
$invalid_chars = Array(
'"',
"'",
'|',
'/',
'\\',
'<',
'>',
'?',
',',
'.',
'~',
'!',
'@',
'#',
'$',
'%',
'^',
'&',
'*',
'(',
')',
'+',
'-',
'=',
';',
':',
'[',
']',
'{',
'}'
);

//Functions for setting, getting and validating a users nickname.

function user_nickname_set($server, $socket, $nickname)
{
return $server->socket_data_set($socket, "nickname", $nickname);
}
function user_nickname_get($server, $socket)
{
$nickname = $server->socket_data_get($socket, "nickname");
if (!$nickname)
$nickname = "";
return $nickname;
}

function user_nickname_clear($server, $socket)
{
return $server->socket_data_clear($socket, "nickname");
}

function user_nickname_find_all($server, $nickname)
{
$sockets = Array();
foreach ($server->sockets() as $socket)
{
if (stripos(user_nickname_get($server, $socket), $nickname) === FALSE)
continue;
$sockets[] = $socket;
}
return $sockets;
}

function user_nickname_find($server, $nickname)
{
if ($nickname === "")
return FALSE;
foreach ($server->sockets() as $socket)
{
if (!strcasecmp($nickname, user_nickname_get($server, $socket)))
return $socket;
}
return FALSE;
}

function user_nickname_validate($server, $socket, $nickname)
{
if ($nickname === "")
return FALSE;
global $invalid_chars;
$result = user_nickname_find($server, $nickname);
if (!$result)
{
//No one is connected using that nickname.
$result = string_find($nickname, $invalid_chars);
if ($result !== FALSE)
{
//Nickname contains invalid characters.
$server->send($socket, "Error: Your nickname cannot contain any of the following:\r\n" . implode(' ', $invalid_chars) . "\r\nPlease choose another.");
return FALSE;
}
//Validation passed.
return TRUE;
}
//Validation failed.
$server->send($socket, "The nickname $nickname is already in use. Please choose another.");
return FALSE;
}
?>
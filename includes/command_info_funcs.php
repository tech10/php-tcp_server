<?php
global $command_info, $command_info_admin;
$command_info = Array();
$command_info_admins = Array();

function command_info_add($command, $description, $examples = FALSE)
{
global $command_info;
if (isset($command_info[$command]))
return FALSE;
$command_info[$command]['description'] = $description;
if ($examples)
$command_info[$command]['examples'] = $examples;
ksort($command_info, SORT_NATURAL);
return TRUE;
}
function command_info_admin_add($command, $description, $examples = FALSE)
{
global $command_info_admin;
if (isset($command_info_admin[$command]))
return FALSE;
$command_info_admin[$command]['description'] = $description;
if ($examples)
$command_info_admin[$command]['examples'] = $examples;
ksort($command_info_admin, SORT_NATURAL);
return TRUE;
}

function command_info($command)
{
global $command_info;
if (!isset($command_info[$command]))
return FALSE;
$msg = "Command: $command\r\n";
$msg .= "Description:\r\n" . $command_info[$command]['description'] . "\r\n";
if (isset($command_info[$command]['examples']))
$msg .= "Examples:\r\n" . $command_info[$command]['examples'] . "\r\n";
return $msg;
}

function command_info_admin($command)
{
global $command_info_admin;
if (!isset($command_info_admin[$command]))
return FALSE;
$msg = "Command: $command\r\n";
$msg .= "Description:\r\n" . $command_info_admin[$command]['description'] . "\r\n";
if (isset($command_info[$command]['examples']))
$msg .= "Examples:\r\n" . $command_info_admin[$command]['examples'] . "\r\n";
return $msg;
}

function command_info_all($server, $socket)
{
global $command_info, $command_info_admin;
$msg = "User commands\r\n\r\n";
foreach ($command_info as $command => $info)
$msg .= $command . "\r\n";
if ($server->socket_data_get($socket, "admin"))
{
$restricted = $server->socket_data_get($socket, "restricted_commands");
$msg_admin = 0;
foreach ($command_info_admin as $command => $info)
{
if (isset($restricted[$command]) && $restricted[$command])
continue;
if (!$msg_admin)
$msg .= "\r\nAdministrator commands\r\n";
$msg .= $command . "\r\n";
$msg_admin++;
}
}
return $msg;
}
?>
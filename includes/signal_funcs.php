<?php
// signal handler function
function sig_handler($signo)
{
global $server;
switch ($signo)
{
case SIGHUP:
// handle restart tasks
//server_restart();
server_log("Restarting server.", TRUE);
$server->disconnect_all("Restarting server.");
exit;
break;
default:
//Handle shutdown signals, since we're only registering those after restart signals.
server_log("Shutting down server.", TRUE);
$server->disconnect_all("Shutting down server.");
exit;
break;
}
}

// setup signal handlers
//Shut down signal handlers.
pcntl_signal(SIGQUIT, "sig_handler");
pcntl_signal(SIGINT, "sig_handler");
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGTSTP, "sig_handler");
pcntl_signal(SIGXCPU, "sig_handler");
//Restart signal handlers.
pcntl_signal(SIGHUP, "sig_handler");
?>

<?php
// signal handler function
function sig_handler($signo)
{
switch ($signo)
{
case SIGHUP:
// handle restart tasks
server_restart();
break;
default:
//Handle shutdown signals, since we're only registering those after restart signals.
server_shutdown();
break;
}
}

// setup signal handlers
if (function_exists("pcntl_signal"))
{
//Shut down signal handlers.
pcntl_signal(SIGQUIT, "sig_handler");
pcntl_signal(SIGINT, "sig_handler");
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGTSTP, "sig_handler");
pcntl_signal(SIGXCPU, "sig_handler");
//Restart signal handlers.
pcntl_signal(SIGHUP, "sig_handler");
}
?>

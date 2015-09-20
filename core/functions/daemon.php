<?php
global $data_dir, $daemon;
$daemon = false;

function daemonize()
{
global $data_dir, $daemon;
if (isset($daemon) && $daemon)
return false;
//These only work on posix systems with pcntl compiled in.
if (!function_exists("pcntl_fork"))
return false;
$pid = pcntl_fork();
if ($pid < 0)
{
return false;
}
else if($pid)
{
//Close the parent so we're detached from the console.
exit(0);
}
//Make this process the session leader, only if posix is available.
//This will let us fork more if we want.
if (function_exists("posix_setsid"))
posix_setsid();
$pid = getmypid();
$daemon = true;
//Close the I/O streams since we're a daemon.
//WARNING: Don't echo data, write an output function wrapper for that purpose.
//If you do, your script will crash with no output.
fclose(STDIN);
fclose(STDOUT);
fclose(STDERR);
if (!file_exists($data_dir))
mkdir($data_dir, 0750, TRUE);
file_put_contents($data_dir . "server.pid", $pid);
return $pid;
}
function is_daemon()
{
global $daemon;
if (!isset($daemon) || !$daemon)
return false;
return $daemon;
}
?>
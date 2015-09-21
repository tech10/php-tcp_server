<?php
//Main function to check times.
function time_checks($server, $socket)
{
//Add an extremely rudimentary keepalive thing.
$server->send_raw($socket, "");
}
?>

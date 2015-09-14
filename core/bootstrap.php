<?php
foreach (glob(__DIR__ . DS . "functions" . DS . "*.php") as $inc_file)
{
require_once($inc_file);
}
spl_autoload_register(function($class) {
require_once(__DIR__ . DS . "classes" . DS . $class . "_class" . DS . $class . "_class.php");
});
$plugin_dir = getcwd() . DS . "plugins" . DS;
if (file_exists($plugin_dir))
{
foreach (scandir($plugin_dir) as $f)
{
if ($f == ".." || $f == ".")
continue;
$fd = $plugin_dir . $f;
if (is_dir($fd))
{
if (file_exists($fd . DS . "$f.php"))
include($fd . DS . "$f.php");
}
if (pathinfo($fd, PATHINFO_EXTENSION) == "php")
include($fd);
}
}
?>

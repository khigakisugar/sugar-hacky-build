<?php

//$HOME = $_SERVER['HOME'];
$HOMEROOT = getenv('HOME');
$HOME = realpath(__DIR__);
$filerelativedir = $argv[1];

$defaultsfile = "$HOME/sugarbuildconfig.ini";
$defaults = array('version' => '', 'flavor' => '',);
if (file_exists($defaultsfile)) {
    $defaults = parse_ini_file($defaultsfile);
} else {
    die("defaults file $defaultsfile does not exist");
}

$defaultversion = $defaults['version'];
$defaultflavor = $defaults['flavor'];

$safeversion = str_replace(".", "_", $defaultversion);

chdir("$HOMEROOT/Mango/build/rome");
$buildcommand = "/usr/bin/php $HOMEROOT/Mango/build/rome/build.php ver=$defaultversion flav=$defaultflavor build_dir=$HOMEROOT/Sites/$safeversion dir=$HOMEROOT/Mango/$filerelativedir --clean=1 --cleanCache=1 --sidecar=1";
error_log("running $buildcommand");
shell_exec("$buildcommand");
?>

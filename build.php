#!/usr/bin/php
<?php

$HOMEROOT = getenv("HOME");
$HOME = getcwd();
require_once($HOME . "/remove.php");
require_once($HOME . "/run.php");

function theMain() {

    global $HOMEROOT, $HOME;
    // get the default values from the ini file
    err_log("pulling defaults from ini file");
    $defaultsfile = "$HOME/sugarbuildconfig.ini";
    $namefile = "$HOME/currentbranch";
    $keyfile = "$HOME/license.ini";
    $key = '';
    list($defaultversion, $defaultflavor, $dbpword) = getDefaults($defaultsfile);
    $name = getDefaultName($namefile);
    if (file_exists($keyfile)) {
        $key = parse_ini_file($keyfile)['key'];
    }

    // get the user input
    list($version, $flavor, $name, $demoData) = getAllUserInput($defaultversion, $defaultflavor, $name);
    $database = "sugar_$name" . "_$flavor";

    // update ini file
    err_log("updating defaults: version=$version, flavor=$flavor, password=$dbpword");
    setDefaults($defaultsfile, $version, $flavor, $name, $dbpword, $namefile);

    // remove the existing install (if it exists)
    err_log("checking if this has already been installed at /Users/khigaki/Sites/$name/$flavor");
    if (file_exists("/Users/khigaki/Sites/$name/$flavor")) {
        err_log("installation found");
        remove($name, $flavor);
    } else {
        err_log("installation not found");
    }

    // Build the config from template
    updateConfigSi($flavor, $version, $name, $demoData, $key);

    // update sidecar
    //updateSubmodules();

    // do a composer or npm update if needed
    updateDependencies($flavor, $version);

    // Run the build
    runBuild($version, $flavor, $name);

    // restart mysql
    run("mysql.server restart");

    // Open a browser window
    run("/usr/bin/open -a '/Applications/Google Chrome.app' --new --args 'http://$name.localhost/$name/$flavor/sugarcrm'");

    // Set permissions on grunt-cli
    run("chmod u+x $HOMEROOT/Sites/$name/$flavor/sugarcrm/sidecar/node_modules/grunt-cli/bin/grunt");
    run("chmod u+x $HOMEROOT/Sites/$name/$flavor/sugarcrm/node_modules/grunt-cli/bin/grunt");

    // Run tests
    // Commented out, since running tests right after tends to cause file handle issues
    //run("./node_modules/grunt-cli/bin/grunt karma:ci", "$HOMEROOT/Mango/sugarcrm/sidecar");
    //run("./node_modules/grunt-cli/bin/grunt karma:ci", "$HOMEROOT/Mango/sugarcrm");
}

// run composer and npm updates in the install
function updateDependencies($flavor, $version) {
    global $HOMEROOT;
    $loc = "$HOMEROOT/Mango/sugarcrm";
    run("composer install", $loc);
    run("npm install", $loc);
    run("npm install", $loc . "/sidecar");
}

function promptUser($prompt, $allowedValues=NULL, $default=NULL) {
    // loop until a valid answer is provided
    while (true) {
        $userValue = strtolower(readline($prompt));
        // if answer is an empty string and default is specified, return default
        if (($userValue == "") && $default){
            err_log("defaulting to $default");
            return $default;
        }
        // check answer against allowed values.
        if ($allowedValues) {
            if (in_array($userValue, $allowedValues)) {
                // valid answer given, return answer
                return $userValue;
            } else {
                // invalid answer given, prompt again
                $validAnswer = implode(", ", $allowedValues);
                err_log("$userValue is not a valid answer. Please answer [$validAnswer]");
                continue;
            }
        } else {
            // all answers valid
            return $userValue;
        }
    }
}

function convertYNToBoolean($yn) {
    if (strtolower($yn) == "y") {
        return true;
    } elseif (strtolower($yn) == "n") {
        return false;
    } else {
        throw new Exception("convertYNToBoolean Error: value passed is not y/n");
    }
}

function getDefaults($defaultsfile) {
    $defaults = array('version' => '', 'flavor' => '',);
    if (file_exists($defaultsfile)) {
        $defaults = parse_ini_file($defaultsfile);
    }
    $defaultversion = $defaults['version'];
    $defaultflavor = $defaults['flavor'];
    $dbpword = $defaults['dbpword'];
    return array($defaultversion, $defaultflavor, $dbpword);
}

function getAllUserInput($defaultversion=NULL, $defaultflavor=NULL, $name) {
    $YNARRAY = array('y', 'n');
    $FLAVORARRAY = array('pro', 'corp', 'ent', 'ult');

    $version = promptUser("Version [$defaultversion]: ", NULL, $defaultversion);
    $flavor = promptUser("Flavor (pro, ent, ult): [$defaultflavor] ", $FLAVORARRAY, $defaultflavor);
    $branchName = promptUser("Branch name [$name]: ", NULL, $name);

    $demoData = convertYNToBoolean(promptUser("Demo Data? (y/N): ", $YNARRAY, 'n'));
    $branchName = str_replace("-", "_", $branchName);

    return array($version, $flavor, $branchName, $demoData);
}

function getDefaultName($namefile) {
    if (file_exists($namefile)) {
       return rtrim(file_get_contents($namefile));
    }
}

function setDefaults($defaultsfile, $version, $flavor, $name, $dbpword, $namefile) {
    $defaultsrewrite = fopen($defaultsfile, 'w');
    fwrite($defaultsrewrite, '[sugar]'. PHP_EOL);
    fwrite($defaultsrewrite, "version=$version" . PHP_EOL);
    fwrite($defaultsrewrite, "flavor=$flavor" . PHP_EOL);
    fwrite($defaultsrewrite, "dbpword=$dbpword" . PHP_EOL);
    fclose($defaultsrewrite);
    if ($name) {
        $name = trim($name);
        $name = explode('~', $name)[0];
        $namewrite = fopen($namefile, 'w');
        fwrite($namewrite, $name);
        fclose($namewrite);
    }
}

function updateConfigSi($flavor, $version, $name, $demoData, $key) {
    global $HOME, $HOMEROOT;
    $configtemplate = "$HOME/config_si.php";
    $configdestination = "$HOMEROOT/Mango/sugarcrm/config_si.php";
    err_log("updating $configdestination");
    if (file_exists($configtemplate)) {
        $readhandle = fopen($configtemplate, 'r');
    } else {
        $readhandle = false;
    }
    $writehandle = fopen($configdestination, 'w');
    if ($readhandle) {
        while ($line=fgets($readhandle)) {
            if (!$line) {
                break;
            }
            $line = str_replace('<FLAV>', $flavor, $line);
            $line = str_replace('<VERS>', $version, $line);
            $line = str_replace('<NAME>', $name, $line);
            $line = str_replace('<DEMO>', $demoData ? "true" : "false", $line);
            $line = str_replace('<KEY>', $key, $line);
            fwrite($writehandle, $line);
        }
        fclose($readhandle);
        fclose($writehandle);
    } else {
        fclose($writehandle);
        die("config template missing from $configTemplate");
    }
}

function updateSubmodules() {
    global $HOMEROOT;
    run("git fetch upstream", "$HOMEROOT/Mango/sugarcrm/sidecar");
    run("git submodule update", "$HOMEROOT/Mango");
}

function runBuild($version, $flavor, $name) {
    global $HOME, $HOMEROOT;
    $buildDir = "$HOMEROOT/Sites/$name";
    if (file_exists($buildDir)) {
        run("rm -r $buildDir");
    }
    //$buildscript = "/usr/bin/php $HOMEROOT/Mango/build/rome/build.php --ver=$version --flav=$flavor --dir=$HOMEROOT/Mango --build_dir=$buildDir --latin=1 --clean --cleanCache --sidecar";
    $buildscript = "/usr/bin/php $HOMEROOT/Mango/build/rome/build.php --ver=$version --flav=$flavor --dir=$HOMEROOT/Mango --build_dir=$buildDir --clean --cleanCache --sidecar";
    run("$buildscript", "$HOMEROOT/Mango/build/rome");
    run("curl -s 'http://localhost/$name/$flavor/sugarcrm/install.php?goto=SilentInstall&cli=true'");
}


theMain();

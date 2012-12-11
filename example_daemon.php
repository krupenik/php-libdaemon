#! /usr/bin/env php
<?php
require_once "daemon.class.php";

class ExampleDaemon extends Daemon {
    protected function run_cycle() {
        $this->log($this->config["message"]);
        sleep(1);
    }
}

$options = getopt("c:h");

if (!(isset($options["c"]) && file_exists($options["c"]))) {
    die("Usage: ". $_SERVER["argv"][0] ." -c config_file\n");
}

$e = new ExampleDaemon($options["c"]);
$e->start();

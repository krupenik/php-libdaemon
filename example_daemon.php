<?php
require_once "daemon.class.php";

class ExampleDaemon extends Daemon {
    protected function run_cycle() {
        $this->log($this->config["message"]);
        sleep(1);
    }
}

$e = new ExampleDaemon("./example.yml");
$e->start();

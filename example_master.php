#! /usr/bin/env php
<?php
require_once "master.class.php";

class ExampleWorker extends Worker
{
    private $started_at;

    function after_construct() {
        $this->started_at = time();
    }

    function run_cycle()
    {
        print (time() - $this->started_at) . "\n";
        sleep(1);
    }
}

class ExampleMaster extends Master
{
    function create_worker_config()
    {
        return array();
    }
}

$options = getopt("c:h");

if (!(isset($options["c"]) && file_exists($options["c"]))) {
    die("Usage: ". $_SERVER["argv"][0] ." -c config_file\n");
}

$e = new ExampleMaster($options["c"]);
$e->start();

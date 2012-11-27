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

$m = new ExampleMaster("./example.yml");
$m->start();
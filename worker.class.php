<?php
abstract class Worker
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->running = true;
        
        $this->after_construct();
    }

    public function start()
    {
        $this->register_signals();
        $this->run();
        $this->shutdown();
    }

    protected function run()
    {
        while ($this->running) {
            pcntl_signal_dispatch();
            $this->run_cycle();
        }
    }

    protected function stop()
    {
        $this->running = false;        
    }    

    protected function after_construct() { }
    protected function after_register_signals() { }
    protected function before_shutdown() { }
    abstract protected function run_cycle();

    protected function register_signals()
    {
        pcntl_signal(SIGTERM, array(&$this, "stop"));
        $this->after_register_signals();
    }

    protected function shutdown()
    {
        $this->before_shutdown();
    }    

}

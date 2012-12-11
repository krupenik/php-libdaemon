<?php
require_once "daemon.class.php";

abstract class Master extends Daemon
{
    protected $children = array();

    public function __construct($config_file)
    {
        parent::__construct($config_file);
    }

    protected function register_signals()
    {
        parent::register_signals();

        pcntl_signal(SIGCHLD, array(&$this, "reap"));
    }

    protected function run_cycle()
    {
        while ($this->config["max_children"] > count($this->children)) {
            $pid = pcntl_fork();

            if (-1 == $pid) {
                $this->log("fork failed!");
            }
            elseif (0 == $pid) {
                $this->open_std_files();

                $worker = new $this->config["worker_class"]($this->create_worker_config());
                $worker->start();
                exit(0);
            }
            else {
                $this->children[$pid] = $pid;

                if ($this->config["debug"]) {
                    $this->log(sprintf("spawned child: %d, total children count: %d", $pid, count($this->children)));
                }
            }
        }

        sleep(1); // 1 should be hardcoded because of signal dispatching
    }

    protected function before_shutdown()
    {
        $this->terminate_children();
    }

    protected function after_configure()
    {
        $this->terminate_children();
    }

    abstract protected function create_worker_config();

    protected function reap()
    {
        if (0 < $pid = pcntl_waitpid(-1, $status, WNOHANG)) {
            unset($this->children[$pid]);
        }
    }

    protected function terminate_children()
    {
        foreach ($this->children as $pid => $data) {
            posix_kill($pid, SIGTERM);
        }
    }
}

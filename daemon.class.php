<?php
require_once "worker.class.php";

abstract class Daemon extends Worker
{
    protected $config = array(
        "daemonize" => true,
        "debug" => false,
    );

    protected $config_file;
    protected $_log;

    public function __construct($config_file)
    {
        $this->config_file = $config_file;
        $this->running = true;
    }

    public function start()
    {
        $this->configure();
        $this->detach();
        $this->open_std_files();

        parent::start();
    }

    protected function log($message)
    {
        fwrite($this->_log, sprintf("%s [%d] %s %s\n", strftime("%F %T"), posix_getpid(), get_class($this), $message));
    }

    protected function shutdown()
    {
        fclose($this->_log);
        if ($this->config["daemonize"]) {
            unlink($this->config["pidfile"]);
        }

        parent::shutdown();
    }

    protected function register_signals()
    {
        parent::register_signals();

        pcntl_signal(SIGHUP, array(&$this, "configure"));
        pcntl_signal(SIGINT, array(&$this, "stop"));
        pcntl_signal(SIGUSR1, array(&$this, "open_std_files"));
    }

    protected function after_configure() { }
    protected function after_detach() { }

    protected function configure()
    {
        if ($this->config["debug"]) {
            $this->log("configuring");
        }

        $this->config = array_merge($this->config, yaml_parse_file($this->config_file));
        $this->after_configure();
    }

    protected function detach()
    {
        if ($this->config["daemonize"]) {
            $pid = pcntl_fork();

            if (-1 == $pid)
                die("fork failed");
            elseif (0 < $pid)
                exit(0);

            if (-1 == posix_setsid())
                die("setsid failed");

            file_put_contents($this->config["pidfile"], posix_getpid() . "\n");
        }

        $this->after_detach();
    }

    protected function open_std_files()
    {
        if ($this->config["daemonize"]) {
            if (is_resource(STDOUT)) fclose(STDOUT);
            if (is_resource(STDERR)) fclose(STDERR);
            if (is_resource(STDIN))  fclose(STDIN);

            fopen("/dev/null", "r");
            fopen($this->config["logfile"], "ab");
            $this->_log = fopen($this->config["logfile"], "ab");
        }
    }
}

<?php

namespace alexlvcom\TaskRunner;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use alexlvcom\TaskRunner\Helpers\Climate;
use alexlvcom\ServiceContainer\Container as ServiceContainer;

abstract class Task extends Command
{
    use PidTrait;

    /**
     * @var Monolog instance
     */
    protected $log;

    /**
     * Log directory
     * @var
     */
    protected $logPath;

    /**
     * Task name - will be set automatically
     * @var
     */
    protected $taskName;

    /**
     * @var \alexlvcom\TaskRunner\Helpers\Climate
     */
    protected $climate;

    /**
     * @var \alexlvcom\TaskRunner\ServiceContainer
     */
    protected $serviceContainer;


    abstract protected function run(CommandContext $context);


    public function execute(CommandContext $context)
    {

        $this->setTaskName();

        // by default logging is not enabled
        if (property_exists($this, 'requireLogging') && static::$requireLogging === true) {
            $this->setLogger();
        }

        // by default multiple run is allowed
        if (property_exists($this, 'multipleRunAllowed') && static::$multipleRunAllowed === false) {
            $this->setPidFilePath($this->getLogPath());
            if ($pid = $this->isProcessRunning()) {
                $this->climate->info($this->taskName.' task is already running with PID '.$pid.'. Exiting...');
                return false;
            }
            $this->createPid();
        }

        $this->climate->info($this->taskName.' task started...');


        // validating required params if there are any
        if (!$this->validate($context)) {
            $this->climate->error($context->getError());
            return false;
        }

        // running the task
        $runResult = $this->run($context);

        if ($runResult === true) {
            $result = $context->getResult();
            if ($result) {
                $this->climate->info($this->taskName." task completed successfully with result: ".json_encode($result), $result);
            } else {
                $this->climate->info($this->taskName." task completed successfully.");
            }
        } elseif ($runResult === false) {
            $error = $context->getError();
            if ($error) {
                $this->climate->error($this->taskName." task ended with error: ".$error);
            } else {
                $this->climate->error($this->taskName." task ended with unexpected error.");
            }
        } else {
            $this->climate->info($this->taskName." task completed.");
        }

        return true;
    }

    private function setTaskName()
    {

        if (property_exists($this, 'name') && static::$name !== '') { // setting name from static variable in task class
            $this->taskName = static::$name;
        } else { // generating task name from class name
            $path           = explode('\\', get_class($this));
            $this->taskName = array_pop($path);
        }
    }

    private function setLogger()
    {

        $this->log = $this->serviceContainer->make('Monolog\Logger', $this->taskName);
        $this->serviceContainer->bind('Monolog\Logger', $this->log);

        $logFileName = $this->taskName;

        if (property_exists($this, 'logName') && static::$logName !== '') {
            $logFileName = static::$logName;
        }

        $logFileName = $this->getLogPath().'/'.strtolower($logFileName).'.log';

        $this->log->pushHandler($this->serviceContainer->make('Monolog\Handler\StreamHandler', $logFileName, Logger::INFO));

        $this->climate->setLogger($this->log);

        $this->serviceContainer->bind('alexlvcom\TaskRunner\Helpers\Climate', $this->climate);
    }


    /**
     * @return mixed
     */
    public function getLogPath()
    {
        return $this->logPath;
    }

    /**
     * @param mixed $logPath
     */
    public function setLogPath($logPath)
    {
        $this->logPath = $logPath;
    }

    /**
     * @return \alexlvcom\TaskRunner\ServiceContainer
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @param \alexlvcom\TaskRunner\ServiceContainer $serviceContainer
     */
    public function setServiceContainer(ServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return Climate
     */
    public function getClimate()
    {
        return $this->climate;
    }

    /**
     * @param Climate $climate
     */
    public function setClimate($climate)
    {
        $this->climate = $climate;
    }
}

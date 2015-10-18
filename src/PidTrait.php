<?php

namespace alexlvcom\TaskRunner;

trait PidTrait
{
    private $pidFilePath;

    public function createPid()
    {
        $pid = posix_getpid();
        file_put_contents($this->getPidFilename(), $pid);
        register_shutdown_function([$this, 'removePid']);
        return $pid;

    }

    public function removePid()
    {
        unlink($this->getPidFilename());
    }

    public function isProcessRunning()
    {

        if (!file_exists($this->getPidFilename()) || !is_file($this->getPidFilename())) {
            return false;
        }
        $pid     = file_get_contents($this->getPidFilename());
        $running = posix_kill($pid, 0);
        return $running ? $pid : false;

        return false;
    }

    public function getPidFilename()
    {
        return $this->getPidFilePath().'/'.strtolower($this->taskName).'.pid';
    }

    /**
     * @return mixed
     */
    public function getPidFilePath()
    {
        return $this->pidFilePath;
    }

    /**
     * @param mixed $pidFilePath
     */
    public function setPidFilePath($pidFilePath)
    {
        $this->pidFilePath = $pidFilePath;
    }
}

<?php

namespace alexlvcom\TaskRunner;

class CommandContext extends \ArrayObject
{
    private $params = [];

    private $error = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function addParam($key, $val)
    {
        $this[$key] = $val;
    }

    public function get($key)
    {
        return $this[$key];
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setResult($data)
    {
        $this['result'] = $data;
    }

    public function getResult()
    {
        return array_key_exists('result', $this) ? $this['result'] : null;
    }
}

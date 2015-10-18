<?php

namespace alexlvcom\TaskRunner\Tasks;

use alexlvcom\TaskRunner\CommandContext;
use alexlvcom\TaskRunner\Task;

class MyTask extends Task
{
    protected static $multipleRunAllowed = false;
    protected static $requireLogging = true;

    public static $params = [
        'param1' => 'required|integer',
        'param2' => 'default:test|alpha',
    ];

    protected function run(CommandContext $context)
    {

        dump($context);

        $context->setResult(['foo', 'bar']);

        return true;
    }
}

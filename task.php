<?php

/**
 * Task runner by AlexLV.
 * @version 0.0.2
 *
 * (c) alexlvcom 2015
 *
 * TODO: CHANGELOG:
 *
 * v 0.0.2:
 * Input arguments and validation handling refactoring. Laravel Validator is used to validate. Plus added ability to set default values for optional arguments.
 * v 0.0.1:
 * First release
 *
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/vendor/autoload.php';

define('ALEXLVCOM_TASK_RUNNER_VERSION', '0.0.2');
define('TASK_PATH', __DIR__.'/app/tasks');
define('TASK_LOG_PATH', __DIR__.'/app/logs');
define('ROOTPATH', __DIR__.'/');


use League\CLImate\CLImate;
use alexlvcom\TaskRunner\CommandContext;
use alexlvcom\TaskRunner\ParamValidator;
use alexlvcom\ServiceContainer\Container as ServiceContainer;

$climate   = new CLImate();
$container = new ServiceContainer();

$climate->out('<green>Task Runner by AlexLV. (c) 2015 .</green> <white>Version <yellow>'.ALEXLVCOM_TASK_RUNNER_VERSION.'</yellow>');
$climate->br();


$climate->arguments->add([
    'name' => [
        'longPrefix'  => 'name',
        'description' => 'Task Name',
        'required'    => true
    ],
]);

try {
    $climate->arguments->parse();
} catch (Exception $e) {
    $climate->error($e->getMessage())->br()->usage();
    $climate->br();
    exit;
}

if ($climate->arguments->defined('name') === false || $climate->arguments->get('name') === '') {
    $climate->usage();
    exit;
}


$time_start = microtime(true);


$taskName = $climate->arguments->get('name');

if (substr($taskName, 0, 1) === '\\') {  // Absolute namespace provided
    $className = substr($taskName, 1);
} else { // relative path goes with prepended alexlvcom\TaskRunner\Tasks namespace
    $className = 'alexlvcom\TaskRunner\Tasks\\'.$climate->arguments->get('name');
}

if (class_exists($className)) {
    $task = $container->make($className);
    $task->setServiceContainer($container);
    $task->setDocroot(ROOTPATH);
    $task->setClimate(new \alexlvcom\TaskRunner\Helpers\Climate());
    $task->setLogPath(TASK_LOG_PATH);

    if ($task instanceof alexlvcom\TaskRunner\Task) {
        if (property_exists($className, 'params')) {
            $paramValidator = new ParamValidator();

            foreach ($className::$params as $paramName => $paramRules) {
                if (in_array('required', explode('|', $paramRules))) {
                    $climate->arguments->add([
                        $paramName => [
                            'longPrefix' => $paramName,
                            'required'   => true,
                        ],
                    ]);
                } else {
                    $argument = [
                        'longPrefix' => $paramName,
                        'required'   => false,
                    ];
                    $defValue = $paramValidator->findDefaultValue($paramRules);
                    if ($defValue !== false) {
                        $argument['defaultValue'] = $defValue;
                    }
                    $climate->arguments->add([$paramName => $argument]);
                }
            }

        }

        try {
            $climate->arguments->parse();
        } catch (Exception $e) {
            $climate->error($e->getMessage())->br()->usage();
            $climate->br();
            exit;
        }

        $context = new CommandContext();

        foreach ($climate->arguments->all() as $argument) {
            if ($argument->name() !== 'name') {
                $context->addParam($argument->name(), $argument->value());
            }
        }

        $task->execute($context);
    } else {
        $climate->error("$className is not instance of alexlvcom\\TaskRunner\\Task.");
    }
} else {
    $climate->error("$className class does not exist.");
}


$time_end = microtime(true);
$time     = round($time_end - $time_start, 4);

$climate->br();
$climate->out('<yellow>All done.</yellow> Took '.$time.'s');

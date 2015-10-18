# Task Runner - command line task runner.
---

### Features
 - Prevents running the same task if it is already been running
 - Arguments validation using Laravel Validator library
 - Ability to set default values for arguments
 - Automatic logging to my/log/path/{mytask}.log

### Dependencies:
- [CLImate](https://github.com/thephpleague/climate)
- [alexlvcom\ServiceContainer](https://github.com/alexlvcom/ServiceContainer)
- [illuminate\validation](https://github.com/illuminate/validation)
- [Monolog](https://github.com/Seldaek/monolog)
- PHP >= 5.6

### Installation
- `composer install`

### Usage

Create task:

```
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

```

Run it:

```
$ php task.php --name=MyTask --param1=123                                
Task Runner by AlexLV. (c) 2015 . Version 0.0.2

MyTask task started...
alexlvcom\TaskRunner\CommandContext {#33
  -params: []
  -error: ""
  flag::STD_PROP_LIST: false
  flag::ARRAY_AS_PROPS: false
  iteratorClass: "ArrayIterator"
  storage: array:2 [
    "param1" => "123"
    "param2" => "test"
  ]
}
MyTask task completed successfully with result: ["foo","bar"]

All done. Took 0.0427s


```

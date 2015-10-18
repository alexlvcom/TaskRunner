<?php

namespace alexlvcom\TaskRunner\Helpers;

use League\CLImate\Util\Writer\WriterInterface;
use Psr\Log\LoggerInterface;

class ClimateMonologWriter implements WriterInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    private $context;
    private $method;

    public function __construct(LoggerInterface $logger, $method = 'info', array $context = [])
    {
        $this->logger  = $logger;
        $this->method  = $method;
        $this->context = $context;
    }

    public function write($content)
    {
        $method = in_array($this->method, [
            'emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug', 'log'
        ], true) ? $this->method : 'notice';

        $this->logger->$method($content, $this->context);
    }
}

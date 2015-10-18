<?php

namespace alexlvcom\TaskRunner\Helpers;

use \Psr\Log\LoggerInterface;

class Climate
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __call($name, $args)
    {
        $climate = new \League\CLImate\CLImate();

        $writerIds = ['out'];

        if (($logger = $this->getLogger()) instanceof LoggerInterface) {
            $context = array_key_exists(1, $args) ? $args[1] : [];
            $climate->output->add('logger', new ClimateMonologWriter($logger, $name, $context));
            $writerIds[] = 'logger';
        }

        $climate->output->defaultTo($writerIds);

        return $climate->{$name}(...$args);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}

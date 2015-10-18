<?php

namespace alexlvcom\TaskRunner;

abstract class Command
{
    abstract public function execute(CommandContext $context);

    public function validate(CommandContext $context)
    {

        if (property_exists($this, 'params') && is_array(static::$params)) {
            $paramValidator = new ParamValidator();

            // Validate input parameters
            $validationErrors = $paramValidator->validate((array)$context, static::$params);

            if (!empty($validationErrors)) {
                $context->setError(current($validationErrors)[0]);
                return false;
            }

            // Set default values for optional parameters
            $newContext = $paramValidator->setDefaultValues((array)$context, static::$params);
            $context->exchangeArray($newContext);
        }

        return true;
    }
}

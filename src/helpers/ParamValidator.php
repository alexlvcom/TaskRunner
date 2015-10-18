<?php

namespace alexlvcom\TaskRunner;

use \Illuminate\Validation\Factory as IlluminateValidationFactory;
use \Symfony\Component\Translation\Translator as SymfonyTranslator;

/**
 * @package alexlvcom
 */
class ParamValidator
{
    // todo:: add more messages from http://laravel.com/docs/5.1/validation
    private $validationMessages = [
        'required'     => ':attribute is required.'
        , 'email'      => ':attribute is not an email address.'
        , 'min'        => ':attribute must be at least :min characters long.'
        , 'numeric'    => ':attribute must be numeric.'
        , 'integer'    => ':attribute must be an integer.'
        , 'url'        => ':attribute is not correct URL.'
        , 'unique'     => ':attribute must be unique.'
        , 'max'        => ':attribute must be no more than :max characters.'
        , 'size'       => ':attribute has to much characters.'
        , 'alpha'      => ':attribute must consist from alphabetic characters.'
        , 'alpha_dash' => ':attribute must consist from alpha-numeric characters, as well as dashes and underscores.'
        //, 'default' => ':attribute is an optional parameter and it has default value of :defaultValue'
    ];

    /**
     * @param array $input
     * @param array $rules
     * @return array
     */
    public function validate(array $input, array $rules)
    {
        $validatorFactory = new IlluminateValidationFactory(new SymfonyTranslator('en'));

        // enabling rule default, which will be used not to validate, but to set default value for optional parameter
        $validatorFactory->extend('default', function ($attribute, $value, $parameters) {
            return count($parameters) == 1 && !empty($parameters[0]);
        });

        // create replacers for custom messages
        //$validatorFactory->replacer('default', function ($message, $attribute, $rule, $parameters) {
        //    $replacement = !empty($parameters[0]) ? $replacement[0] : 'MISSING';
        //    return str_replace(':defaultValue', $replacement, $message);
        //});

        $validator        = $validatorFactory->make($input, $rules, $this->validationMessages);
        $validationErrors = $validator->errors()->getMessages();

        reset($validationErrors);

        return $validationErrors;
    }


    /**
     * set default values for optional parameters if value is not passed in by user
     * @param array $input
     * @param array $rules
     * @return array
     */
    public function setDefaultValues(array $input, array $rules)
    {
        foreach ($rules as $paramName => $paramRules) {
            if (!array_key_exists($paramName, $input) || $input[$paramName] === null) {
                $defaultValue = $this->findDefaultValue($paramRules);
                if ($defaultValue) {
                    $input[$paramName] = $defaultValue;
                }
            }
        }
        return $input;
    }

    /**
     * Retrives default value if it is set in the rules
     * @param string $nestedRules e.g.  "numeric|default:10"
     * @return bool / string
     */
    public function findDefaultValue($nestedRules)
    {
        if (strpos($nestedRules, 'default:') !== false) {
            $defParam = array_filter(explode('|', $nestedRules), function ($a) {
                return strpos($a, 'default:') === 0 ? $a : false;
            });
            if (!empty($defParam)) {
                return explode(':', current($defParam))[1];
            }
        }

        return false;
    }
}

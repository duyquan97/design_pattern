<?php

namespace App\Exception;

/**
 * Class FormValidationException
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class FormValidationException extends CmHubException
{
    const MESSAGE = 'Form got error';
    const TYPE = 'form_validation';

    /**
     * @var array
     */
    private $errors;

    /**
     * FormValidationException constructor.
     *
     * @param array $errors
     */
    public function __construct(array $errors = array())
    {
        parent::__construct(static::MESSAGE, 400);

        $this->errors = $errors;
    }

    /**
     *
     * @return string
     */
    public function getExceptionType(): string
    {
        return static::TYPE;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

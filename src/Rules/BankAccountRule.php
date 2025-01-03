<?php

namespace Condoedge\Eft\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

use Illuminate\Support\Facades\Validator;

class BankAccountRule implements DataAwareRule, Rule
{
    protected $data = [];
    public static $institutionAccountRules = [
        '001' => 7,
        '002' => [7,12],
        '003' => [7,12],
        '004' => 7,
        '006' => 7,
        '010' => 7,
        '016' => [9,12],
        '030' => 9,
        '540' => 10,
        '614' => 10,
        '623' => 2,
        '815' => 8,
        '889' => [7,12],
    ];
    protected $errorMessage;

    protected $institutionLabel;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($institutionLabel = null)
    {
        $this->institutionLabel = $institutionLabel ?: 'institution';
    }

    public function setData($data)
    {
        $this->data = $data;
 
        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$value) {
            $this->errorMessage = __('The account number is required');
            return false;
        }

        if (!preg_match('/^[0-9]*$/', $value)) {
            $this->errorMessage = __('The account number should be all numbers');
            return false;
        }

        $institution = $this->data[$this->institutionLabel] ?? null;

        $addRule = static::$institutionAccountRules[$institution] ?? null;

        if (!$addRule) {
            $this->errorMessage = 'Please fill the institution field correctly';
            return false;
        }

        if (is_int($addRule) && (strlen($value) != $addRule)) {
            $this->errorMessage = 'The account number should be '.$addRule.' digits';
            return false;
        }

        if (is_array($addRule) && ((strlen($value) < $addRule[0]) || (strlen($value) > $addRule[1]))) {
            $this->errorMessage = 'This should be between '.$addRule[0].' and '.$addRule[1].' digits';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return  $this->errorMessage;
    }
}

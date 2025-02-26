<?php

namespace Condoedge\Eft\Rules;

use Illuminate\Contracts\Validation\Rule;

class BankInstitutionRule implements Rule
{
    protected $errorMessage;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
            $this->errorMessage = __('The institution number is required');
            return false;
        }

        if (!preg_match('/^[0-9]{3}$/', $value)) {
            $this->errorMessage = __('The institution should be 3 numbers');
            return false;
        }

        if (!in_array($value, array_keys(BankAccountRule::$institutionAccountRules))) {
            $this->errorMessage = __('The institution is not one of the recognized values.');
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
        return $this->errorMessage;
    }
}

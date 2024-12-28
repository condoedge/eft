<?php

namespace Condoedge\Eft\Rules;

use Illuminate\Contracts\Validation\Rule;

class BankTransitRule implements Rule
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
            $this->errorMessage = __('The transit/branch number is required');
            return false;
        }

        if (!preg_match('/^[0-9]{5}$/', $value)) {
            $this->errorMessage = __('The institution should be 5 numbers');
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

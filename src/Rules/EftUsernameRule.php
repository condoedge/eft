<?php

namespace Condoedge\Eft\Rules;

use Illuminate\Contracts\Validation\Rule;

class EftUsernameRule implements Rule
{
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
        return strlen($value) == 5 || strlen($value) == 10;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('The EFT username should be either 5 or 10 characters.');
    }
}

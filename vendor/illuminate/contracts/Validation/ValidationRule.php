<?php

namespace RectorPrefix202407\Illuminate\Contracts\Validation;

use Closure;
interface ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, $value, Closure $fail) : void;
}

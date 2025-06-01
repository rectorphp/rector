<?php

namespace RectorPrefix202506\Illuminate\Contracts\Validation;

use Closure;
interface ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, $value, Closure $fail) : void;
}

<?php

declare(strict_types=1);

namespace Illuminate\Foundation\Http;

if (class_exists('Illuminate\Foundation\Http\FormRequest')) {
    return;
}

class FormRequest
{
}

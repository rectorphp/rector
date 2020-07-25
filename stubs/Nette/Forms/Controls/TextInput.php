<?php

declare(strict_types=1);

namespace Nette\Forms\Controls;

if (class_exists('Nette\Forms\Controls\TextInput')) {
    return;
}

final class TextInput
{
    public $value;
}

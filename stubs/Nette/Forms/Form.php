<?php

declare(strict_types=1);

namespace Nette\Forms;

use Nette\ComponentModel\Container;

if (class_exists('Nette\Forms\Form')) {
    return;
}

class Form extends Container
{
    public function addText(string $name, $label = null, int $cols = null, int $maxLength = null): Controls\TextInput
    {
    }
}

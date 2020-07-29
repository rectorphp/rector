<?php

declare(strict_types=1);

namespace Nette\Localization;

if (interface_exists('Nette\Localization\ITranslator')) {
    return;
}

interface ITranslator
{
    public function translate($message, $count = null);
}

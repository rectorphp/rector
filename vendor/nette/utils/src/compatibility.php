<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220321\Nette\Utils;

use RectorPrefix20220321\Nette;
if (\false) {
    /** @deprecated use Nette\HtmlStringable */
    interface IHtmlString extends \RectorPrefix20220321\Nette\HtmlStringable
    {
    }
} elseif (!\interface_exists(\RectorPrefix20220321\Nette\Utils\IHtmlString::class)) {
    \class_alias(\RectorPrefix20220321\Nette\HtmlStringable::class, \RectorPrefix20220321\Nette\Utils\IHtmlString::class);
}
namespace RectorPrefix20220321\Nette\Localization;

if (\false) {
    /** @deprecated use Nette\Localization\Translator */
    interface ITranslator extends \RectorPrefix20220321\Nette\Localization\Translator
    {
    }
} elseif (!\interface_exists(\RectorPrefix20220321\Nette\Localization\ITranslator::class)) {
    \class_alias(\RectorPrefix20220321\Nette\Localization\Translator::class, \RectorPrefix20220321\Nette\Localization\ITranslator::class);
}

<?php

declare (strict_types=1);
/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
namespace RectorPrefix202605\Nette\Utils;

use RectorPrefix202605\Nette;
if (\false) {
    /** @deprecated use Nette\HtmlStringable */
    interface IHtmlString extends Nette\HtmlStringable
    {
    }
} elseif (!interface_exists(IHtmlString::class)) {
    class_alias(Nette\HtmlStringable::class, IHtmlString::class);
}
namespace RectorPrefix202605\Nette\Localization;

if (\false) {
    /** @deprecated use Nette\Localization\Translator */
    interface ITranslator extends Translator
    {
    }
} elseif (!interface_exists(ITranslator::class)) {
    class_alias(Translator::class, ITranslator::class);
}

<?php

declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace NettePostfixedToUniqueAutoload\Utils;

/**
 * The exception that indicates invalid image file.
 */
class UnknownImageFileException extends RegexpException
{
}

/**
 * The exception that indicates error of the last Regexp execution.
 */
class RegexpException extends \Exception
{
    public const MESSAGES = [
        PREG_INTERNAL_ERROR => 'Internal error',
        PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit was exhausted',
        PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted',
        PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 data',
        PREG_BAD_UTF8_OFFSET_ERROR => 'Offset didn\'t correspond to the begin of a valid UTF-8 code point',
        6 => 'Failed due to limited JIT stack space', // PREG_JIT_STACKLIMIT_ERROR
    ];
}

?>
-----
<?php

declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace NettePostfixedToUniqueAutoload\Utils;


?>

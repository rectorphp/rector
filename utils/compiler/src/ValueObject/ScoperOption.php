<?php

declare(strict_types=1);

namespace Rector\Compiler\ValueObject;

/**
 * Based on https://github.com/humbug/php-scoper#configuration
 */
final class ScoperOption
{
    /**
     * @var string
     */
    final public const FINDERS = 'finders';

    /**
     * @var string
     */
    final public const PATCHERS = 'patchers';

    /**
     * @var string
     */
    final public const WHITELIST = 'whitelist';

    /**
     * @var string
     */
    final public const FILES_WHITELIST = 'files-whitelist';

    /**
     * @var string
     */
    final public const PREFIX = 'prefix';
}

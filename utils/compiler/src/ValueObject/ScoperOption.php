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
    public const FINDERS = 'finders';

    /**
     * @var string
     */
    public const PATCHERS = 'patchers';

    /**
     * @var string
     */
    public const WHITELIST = 'whitelist';

    /**
     * @var string
     */
    public const FILES_WHITELIST = 'files-whitelist';
}

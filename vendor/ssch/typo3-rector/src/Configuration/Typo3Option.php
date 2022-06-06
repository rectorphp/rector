<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Configuration;

final class Typo3Option
{
    /**
     * @var string
     */
    public const PHPSTAN_FOR_RECTOR_PATH = __DIR__ . '/../../utils/phpstan/config/extension.neon';
    /**
     * @var string
     */
    public const PATHS_FULL_QUALIFIED_NAMESPACES = 'paths_full_qualified_namespaces';
}

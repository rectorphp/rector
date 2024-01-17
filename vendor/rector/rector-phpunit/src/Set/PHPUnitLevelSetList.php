<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 * @deprecated Instead of too bloated and overriding level sets, use only latest PHPUnit set
 */
final class PHPUnitLevelSetList implements SetListInterface
{
    /**
     * @deprecated Instead of too bloated and overriding level sets, use only latest PHPUnit set
     * @var string
     */
    public const UP_TO_PHPUNIT_50 = __DIR__ . '/../../config/sets/level/deprecated-level-set.php';
    /**
     * @deprecated Instead of too bloated and overriding level sets, use only latest PHPUnit set
     * @var string
     */
    public const UP_TO_PHPUNIT_60 = __DIR__ . '/../../config/sets/level/deprecated-level-set.php';
    /**
     * @deprecated Instead of too bloated and overriding level sets, use only latest PHPUnit set
     * @var string
     */
    public const UP_TO_PHPUNIT_70 = __DIR__ . '/../../config/sets/level/deprecated-level-set.php';
    /**
     * @deprecated Instead of too bloated and overriding level sets, use only latest PHPUnit set
     * @var string
     */
    public const UP_TO_PHPUNIT_80 = __DIR__ . '/../../config/sets/level/deprecated-level-set.php';
    /**
     * @deprecated Instead of too bloated and overriding level sets, use only latest PHPUnit set
     * @var string
     */
    public const UP_TO_PHPUNIT_90 = __DIR__ . '/../../config/sets/level/deprecated-level-set.php';
    /**
     * @deprecated Instead of too bloated and overriding level sets, use only latest PHPUnit set
     * @var string
     */
    public const UP_TO_PHPUNIT_100 = __DIR__ . '/../../config/sets/level/deprecated-level-set.php';
}

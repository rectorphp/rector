<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Set;

use Rector\Set\Contract\SetListInterface;
final class PHPUnitLevelSetList implements \Rector\Set\Contract\SetListInterface
{
    /**
     * @var string
     */
    public const UP_TO_PHPUNIT_50 = __DIR__ . '/../../config/sets/level/up-to-phpunit-50.php';
    /**
     * @var string
     */
    public const UP_TO_PHPUNIT_60 = __DIR__ . '/../../config/sets/level/up-to-phpunit-60.php';
    /**
     * @var string
     */
    public const UP_TO_PHPUNIT_70 = __DIR__ . '/../../config/sets/level/up-to-phpunit-70.php';
    /**
     * @var string
     */
    public const UP_TO_PHPUNIT_80 = __DIR__ . '/../../config/sets/level/up-to-phpunit-80.php';
    /**
     * @var string
     */
    public const UP_TO_PHPUNIT_90 = __DIR__ . '/../../config/sets/level/up-to-phpunit-90.php';
    /**
     * @var string
     */
    public const UP_TO_PHPUNIT_100 = __DIR__ . '/../../config/sets/level/up-to-phpunit-100.php';
}

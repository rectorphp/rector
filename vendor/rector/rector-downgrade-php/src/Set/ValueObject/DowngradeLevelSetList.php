<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

/**
 * @api
 * @deprecated use ->withDowngradeSets() in rector.php instead
 */
final class DowngradeLevelSetList
{
    /**
     * @var string
     */
    public const DOWN_TO_PHP_84 = __DIR__ . '/../../../config/set/level/down-to-php84.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_83 = __DIR__ . '/../../../config/set/level/down-to-php83.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_82 = __DIR__ . '/../../../config/set/level/down-to-php82.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_81 = __DIR__ . '/../../../config/set/level/down-to-php81.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_80 = __DIR__ . '/../../../config/set/level/down-to-php80.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_74 = __DIR__ . '/../../../config/set/level/down-to-php74.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_73 = __DIR__ . '/../../../config/set/level/down-to-php73.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_72 = __DIR__ . '/../../../config/set/level/down-to-php72.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_71 = __DIR__ . '/../../../config/set/level/down-to-php71.php';
}

<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

/**
 * @api
 * @deprecated use ->withDowngradeSets() in rector.php instead
 */
final class DowngradeSetList
{
    /**
     * @var string
     */
    public const PHP_72 = __DIR__ . '/../../../config/set/downgrade-php72.php';
    /**
     * @var string
     */
    public const PHP_73 = __DIR__ . '/../../../config/set/downgrade-php73.php';
    /**
     * @var string
     */
    public const PHP_74 = __DIR__ . '/../../../config/set/downgrade-php74.php';
    /**
     * @var string
     */
    public const PHP_80 = __DIR__ . '/../../../config/set/downgrade-php80.php';
    /**
     * @var string
     */
    public const PHP_81 = __DIR__ . '/../../../config/set/downgrade-php81.php';
    /**
     * @var string
     */
    public const PHP_82 = __DIR__ . '/../../../config/set/downgrade-php82.php';
    /**
     * @var string
     */
    public const PHP_83 = __DIR__ . '/../../../config/set/downgrade-php83.php';
    /**
     * @var string
     */
    public const PHP_84 = __DIR__ . '/../../../config/set/downgrade-php84.php';
    /**
     * @var string
     */
    public const PHP_85 = __DIR__ . '/../../../config/set/downgrade-php85.php';
}

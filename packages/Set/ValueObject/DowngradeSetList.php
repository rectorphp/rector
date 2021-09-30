<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

use Rector\Set\Contract\SetListInterface;
final class DowngradeSetList implements \Rector\Set\Contract\SetListInterface
{
    /**
     * @var string
     */
    public const PHP_53 = __DIR__ . '/../../../config/set/downgrade-php53.php';
    /**
     * @var string
     */
    public const PHP_70 = __DIR__ . '/../../../config/set/downgrade-php70.php';
    /**
     * @var string
     */
    public const PHP_71 = __DIR__ . '/../../../config/set/downgrade-php71.php';
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
}

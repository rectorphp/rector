<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

use Rector\Set\Contract\SetListInterface;
final class DowngradeLevelSetList implements \Rector\Set\Contract\SetListInterface
{
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
    /**
     * @var string
     */
    public const DOWN_TO_PHP_70 = __DIR__ . '/../../../config/set/level/down-to-php70.php';
    /**
     * @var string
     */
    public const DOWN_TO_PHP_56 = __DIR__ . '/../../../config/set/level/down-to-php56.php';
}

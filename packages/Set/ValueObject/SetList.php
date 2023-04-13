<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 */
final class SetList implements SetListInterface
{
    /**
     * @var string
     */
    public const ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION = __DIR__ . '/../../../config/set/action-injection-to-constructor-injection.php';
    /**
     * @var string
     */
    public const CODE_QUALITY = __DIR__ . '/../../../config/set/code-quality.php';
    /**
     * @var string
     */
    public const CODING_STYLE = __DIR__ . '/../../../config/set/coding-style.php';
    /**
     * @var string
     */
    public const DEAD_CODE = __DIR__ . '/../../../config/set/dead-code.php';
    /**
     * @var string
     */
    public const GMAGICK_TO_IMAGICK = __DIR__ . '/../../../config/set/gmagick-to-imagick.php';
    /**
     * @var string
     */
    public const MYSQL_TO_MYSQLI = __DIR__ . '/../../../config/set/mysql-to-mysqli.php';
    /**
     * @var string
     */
    public const NAMING = __DIR__ . '/../../../config/set/naming.php';
    /**
     * @var string
     */
    public const PHP_52 = __DIR__ . '/../../../config/set/php52.php';
    /**
     * @var string
     */
    public const PHP_53 = __DIR__ . '/../../../config/set/php53.php';
    /**
     * @var string
     */
    public const PHP_54 = __DIR__ . '/../../../config/set/php54.php';
    /**
     * @var string
     */
    public const PHP_55 = __DIR__ . '/../../../config/set/php55.php';
    /**
     * @var string
     */
    public const PHP_56 = __DIR__ . '/../../../config/set/php56.php';
    /**
     * @var string
     */
    public const PHP_70 = __DIR__ . '/../../../config/set/php70.php';
    /**
     * @var string
     */
    public const PHP_71 = __DIR__ . '/../../../config/set/php71.php';
    /**
     * @var string
     */
    public const PHP_72 = __DIR__ . '/../../../config/set/php72.php';
    /**
     * @var string
     */
    public const PHP_73 = __DIR__ . '/../../../config/set/php73.php';
    /**
     * @var string
     */
    public const PHP_74 = __DIR__ . '/../../../config/set/php74.php';
    /**
     * @var string
     */
    public const PHP_80 = __DIR__ . '/../../../config/set/php80.php';
    /**
     * @var string
     */
    public const PHP_81 = __DIR__ . '/../../../config/set/php81.php';
    /**
     * @var string
     */
    public const PHP_82 = __DIR__ . '/../../../config/set/php82.php';
    /**
     * @var string
     */
    public const PRIVATIZATION = __DIR__ . '/../../../config/set/privatization.php';
    /**
     * @var string
     */
    public const PSR_4 = __DIR__ . '/../../../config/set/psr-4.php';
    /**
     * @var string
     */
    public const TYPE_DECLARATION = __DIR__ . '/../../../config/set/type-declaration.php';
    /**
     * @var string
     */
    public const EARLY_RETURN = __DIR__ . '/../../../config/set/early-return.php';
    /**
     * @var string
     */
    public const INSTANCEOF = __DIR__ . '/../../../config/set/instanceof.php';
}

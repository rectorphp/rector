<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

/**
 * @api
 */
final class SetList
{
    /**
     * @internal
     * @var string
     */
    public const PHP_POLYFILLS = __DIR__ . '/../../../config/set/php-polyfills.php';
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
     * @deprecated Niche set for a rarely used extension, it is empty now and will be removed.
     * Register RenameClassRector and RenameMethodRector with your own configuration instead.
     * @var string
     */
    public const GMAGICK_TO_IMAGICK = __DIR__ . '/../../../config/set/gmagick-to-imagick.php';
    /**
     * @var string
     */
    public const NAMING = __DIR__ . '/../../../config/set/naming.php';
    /**
     * @var string
     */
    public const NAMED_ARGS = __DIR__ . '/../../../config/set/named-args.php';
    /**
     * Opinionated rules that match rector coding standard
     * @var string
     */
    public const RECTOR_PRESET = __DIR__ . '/../../../config/set/rector-preset.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_52 = __DIR__ . '/../../../config/set/php52.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_53 = __DIR__ . '/../../../config/set/php53.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_54 = __DIR__ . '/../../../config/set/php54.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_55 = __DIR__ . '/../../../config/set/php55.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_56 = __DIR__ . '/../../../config/set/php56.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_70 = __DIR__ . '/../../../config/set/php70.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_71 = __DIR__ . '/../../../config/set/php71.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_72 = __DIR__ . '/../../../config/set/php72.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_73 = __DIR__ . '/../../../config/set/php73.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_74 = __DIR__ . '/../../../config/set/php74.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_80 = __DIR__ . '/../../../config/set/php80.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_81 = __DIR__ . '/../../../config/set/php81.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_82 = __DIR__ . '/../../../config/set/php82.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_83 = __DIR__ . '/../../../config/set/php83.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_84 = __DIR__ . '/../../../config/set/php84.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_85 = __DIR__ . '/../../../config/set/php85.php';
    /**
     * @deprecated Use withPhpSets() or withPhpLevel() instead
     * @var string
     */
    public const PHP_86 = __DIR__ . '/../../../config/set/php86.php';
    /**
     * @var string
     */
    public const PRIVATIZATION = __DIR__ . '/../../../config/set/privatization.php';
    /**
     * @var string
     */
    public const TYPE_DECLARATION = __DIR__ . '/../../../config/set/type-declaration.php';
    /**
     * @var string
     */
    public const TYPE_DECLARATION_DOCBLOCKS = __DIR__ . '/../../../config/set/type-declaration-docblocks.php';
    /**
     * @deprecated Use code-quality set instead, as all early return rules were moved there
     * @var string
     */
    public const EARLY_RETURN = __DIR__ . '/../../../config/set/early-return.php';
    /**
     * @deprecated Use code-quality set instead, as most instanceof rules were moved there
     * @var string
     */
    public const INSTANCEOF = __DIR__ . '/../../../config/set/instanceof.php';
    /**
     * @deprecated Use code-quality and coding-style sets instead, as the if rules were moved there or deprecated
     * @var string
     */
    public const IF = __DIR__ . '/../../../config/set/if.php';
    /**
     * @var string
     */
    public const CARBON = __DIR__ . '/../../../config/set/datetime-to-carbon.php';
    /**
     * @var string
     */
    public const BEHAT_ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../../config/set/behat-annotations-to-attributes.php';
    /**
     * All PHP version rules in one set; each rule gates itself by PHP version at runtime
     * @var string
     */
    public const PHP_VERSION_BASED_SET = __DIR__ . '/../../../config/set/php-version-based.php';
}

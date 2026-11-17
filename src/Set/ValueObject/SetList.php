<?php

declare (strict_types=1);
namespace Rector\Set\ValueObject;

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
     * @var string
     */
    public const NAMED_ARGS = __DIR__ . '/../../../config/set/named-args.php';
    /**
     * Opinionated rules that match rector coding standard
     * @var string
     */
    public const RECTOR_PRESET = __DIR__ . '/../../../config/set/rector-preset.php';
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

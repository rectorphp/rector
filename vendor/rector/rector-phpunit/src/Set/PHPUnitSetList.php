<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Set;

/**
 * @api
 */
final class PHPUnitSetList
{
    /**
     * @var string
     */
    public const PHPUNIT_MOCK_TO_STUB = __DIR__ . '/../../config/sets/phpunit-mock-to-stub.php';
    /**
     * @var string
     */
    public const PHPUNIT_CODE_QUALITY = __DIR__ . '/../../config/sets/phpunit-code-quality.php';
    /**
     * @var string
     */
    public const PHPUNIT_NARROW_ASSERTS = __DIR__ . '/../../config/sets/phpunit-narrow-asserts.php';
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/annotations-to-attributes.php';
    /**
     * @var string
     */
    public const COMPOSER_BASED = __DIR__ . '/../../config/sets/composer-based.php';
}

<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 */
final class PHPUnitSetList implements SetListInterface
{
    /**
     * @deprecated as better outside PHPUnit scope, handle by find and replace when needed
     * @var string
     */
    public const PHPUNIT80_DMS = __DIR__ . '/../../config/sets/phpunit80-dms.php';
    /**
     * @var string
     */
    public const PHPUNIT_40 = __DIR__ . '/../../config/sets/phpunit40.php';
    /**
     * @var string
     */
    public const PHPUNIT_50 = __DIR__ . '/../../config/sets/phpunit50.php';
    /**
     * @var string
     */
    public const PHPUNIT_60 = __DIR__ . '/../../config/sets/phpunit60.php';
    /**
     * @var string
     */
    public const PHPUNIT_70 = __DIR__ . '/../../config/sets/phpunit70.php';
    /**
     * @var string
     */
    public const PHPUNIT_80 = __DIR__ . '/../../config/sets/phpunit80.php';
    /**
     * @var string
     */
    public const PHPUNIT_90 = __DIR__ . '/../../config/sets/phpunit90.php';
    /**
     * @deprecated Use PHPUnitSetList::PHPUNIT_100 directly
     * @var string
     */
    public const PHPUNIT_91 = __DIR__ . '/../../config/sets/phpunit91.php';
    /**
     * @var string
     */
    public const PHPUNIT_100 = __DIR__ . '/../../config/sets/phpunit100.php';
    /**
     * @var string
     */
    public const PHPUNIT_CODE_QUALITY = __DIR__ . '/../../config/sets/phpunit-code-quality.php';
    /**
     * @deprecated Use PHPUnitSetList::PHPUNIT_60 set instead, as related to the version
     * @var string
     */
    public const PHPUNIT_EXCEPTION = __DIR__ . '/../../config/sets/phpunit-exception.php';
    /**
     * @deprecated Use PHPUnitSetList::PHPUNIT_CODE_QUALITY set instead, as related to code-quality
     * @var string
     */
    public const REMOVE_MOCKS = __DIR__ . '/../../config/sets/remove-mocks.php';
    /**
     * @deprecated Use PHPUnitSetList::PHPUNIT_CODE_QUALITY set instead, as related to the code quality
     * @var string
     */
    public const PHPUNIT_SPECIFIC_METHOD = __DIR__ . '/../../config/sets/phpunit-specific-method.php';
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/annotations-to-attributes.php';
}

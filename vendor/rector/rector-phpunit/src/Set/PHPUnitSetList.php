<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Set;

use Rector\Set\Contract\SetListInterface;
final class PHPUnitSetList implements SetListInterface
{
    /**
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
     * @var string
     */
    public const PHPUNIT_91 = __DIR__ . '/../../config/sets/phpunit91.php';
    /**
     * @var string
     */
    public const PHPUNIT_CODE_QUALITY = __DIR__ . '/../../config/sets/phpunit-code-quality.php';
    /**
     * @var string
     */
    public const PHPUNIT_EXCEPTION = __DIR__ . '/../../config/sets/phpunit-exception.php';
    /**
     * @var string
     */
    public const REMOVE_MOCKS = __DIR__ . '/../../config/sets/remove-mocks.php';
    /**
     * @var string
     */
    public const PHPUNIT_SPECIFIC_METHOD = __DIR__ . '/../../config/sets/phpunit-specific-method.php';
    /**
     * @var string
     */
    public const PHPUNIT_YIELD_DATA_PROVIDER = __DIR__ . '/../../config/sets/phpunit-yield-data-provider.php';
}

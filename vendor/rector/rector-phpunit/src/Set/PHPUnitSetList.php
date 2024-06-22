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
    public const PHPUNIT_100 = __DIR__ . '/../../config/sets/phpunit100.php';
    /**
     * @var string
     */
    public const PHPUNIT_110 = __DIR__ . '/../../config/sets/phpunit110.php';
    /**
     * @var string
     */
    public const PHPUNIT_CODE_QUALITY = __DIR__ . '/../../config/sets/phpunit-code-quality.php';
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/annotations-to-attributes.php';
}

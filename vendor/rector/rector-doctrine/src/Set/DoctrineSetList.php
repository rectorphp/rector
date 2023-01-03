<?php

declare (strict_types=1);
namespace Rector\Doctrine\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 */
final class DoctrineSetList implements SetListInterface
{
    /**
     * @var string
     */
    public const DOCTRINE_25 = __DIR__ . '/../../config/sets/doctrine25.php';
    /**
     * @var string
     */
    public const DOCTRINE_BEHAVIORS_20 = __DIR__ . '/../../config/sets/doctrine-behaviors-20.php';
    /**
     * @var string
     */
    public const DOCTRINE_CODE_QUALITY = __DIR__ . '/../../config/sets/doctrine-code-quality.php';
    /**
     * @var string
     */
    public const DOCTRINE_COMMON_20 = __DIR__ . '/../../config/sets/doctrine-common-20.php';
    /**
     * @var string
     */
    public const DOCTRINE_DBAL_210 = __DIR__ . '/../../config/sets/doctrine-dbal-210.php';
    /**
     * @var string
     */
    public const DOCTRINE_DBAL_211 = __DIR__ . '/../../config/sets/doctrine-dbal-211.php';
    /**
     * @var string
     */
    public const DOCTRINE_DBAL_30 = __DIR__ . '/../../config/sets/doctrine-dbal-30.php';
    /**
     * @var string
     */
    public const DOCTRINE_DBAL_40 = __DIR__ . '/../../config/sets/doctrine-dbal-40.php';
    /**
     * @var string
     */
    public const DOCTRINE_REPOSITORY_AS_SERVICE = __DIR__ . '/../../config/sets/doctrine-repository-as-service.php';
    /**
     * @var string
     */
    public const DOCTRINE_ORM_29 = __DIR__ . '/../../config/sets/doctrine-orm-29.php';
    /**
     * @var string
     */
    public const DOCTRINE_ORM_213 = __DIR__ . '/../../config/sets/doctrine-orm-213.php';
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/doctrine-annotations-to-attributes.php';
    /**
     * @var string
     */
    public const GEDMO_ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/doctrine-gedmo-annotations-to-attributes.php';
    /**
     * @var string
     */
    public const DOCTRINE_ODM_23 = __DIR__ . '/../../config/sets/doctrine-odm-23.php';
}

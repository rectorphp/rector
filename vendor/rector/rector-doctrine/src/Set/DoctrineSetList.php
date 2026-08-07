<?php

declare (strict_types=1);
namespace Rector\Doctrine\Set;

/**
 * @api
 */
final class DoctrineSetList
{
    /**
     * @var string
     */
    public const COMPOSER_BASED = __DIR__ . '/../../config/sets/composer-based.php';
    /**
     * @var string
     */
    public const TYPED_COLLECTIONS = __DIR__ . '/../../config/sets/typed-collections.php';
    /**
     * @var string
     */
    public const TYPED_COLLECTIONS_DOCBLOCKS = __DIR__ . '/../../config/sets/typed-collections-docblocks.php';
    /**
     * @var string
     */
    public const DOCTRINE_CODE_QUALITY = __DIR__ . '/../../config/sets/doctrine-code-quality.php';
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/attributes/doctrine.php';
    /**
     * @var string
     */
    public const GEDMO_ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/attributes/gedmo.php';
    /**
     * @var string
     */
    public const MONGODB_ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/attributes/mongodb.php';
}

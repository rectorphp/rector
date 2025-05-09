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
    public const TYPED_COLLECTIONS = __DIR__ . '/../../config/sets/typed-collections.php';
    /**
     * @var string
     */
    public const DOCTRINE_CODE_QUALITY = __DIR__ . '/../../config/sets/doctrine-code-quality.php';
    /**
     * @var string
     */
    public const YAML_TO_ANNOTATIONS = __DIR__ . '/../../config/yaml-to-annotations.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_COMMON_20 = __DIR__ . '/../../config/sets/doctrine-common-20.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_COLLECTION_22 = __DIR__ . '/../../config/sets/doctrine-collection-22.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_DBAL_211 = __DIR__ . '/../../config/sets/doctrine-dbal-211.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_DBAL_30 = __DIR__ . '/../../config/sets/doctrine-dbal-30.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_DBAL_40 = __DIR__ . '/../../config/sets/doctrine-dbal-40.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_ORM_25 = __DIR__ . '/../../config/sets/doctrine-orm-25.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_ORM_28 = __DIR__ . '/../../config/sets/doctrine-orm-28.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_ORM_213 = __DIR__ . '/../../config/sets/doctrine-orm-213.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_ORM_214 = __DIR__ . '/../../config/sets/doctrine-orm-214.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_ORM_219 = __DIR__ . '/../../config/sets/doctrine-orm-219.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_ORM_300 = __DIR__ . '/../../config/sets/doctrine-orm-300.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_BUNDLE_210 = __DIR__ . '/../../config/sets/doctrine-bundle-210.php';
    /**
     * @deprecated Use withComposerBased() instead, see https://getrector.com/blog/introducing-composer-version-based-sets
     * @var string
     */
    public const DOCTRINE_BUNDLE_230 = __DIR__ . '/../../config/sets/doctrine-bundle-23.php';
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
    public const MONGODB__ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/attributes/mongodb.php';
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

/**
 * @api
 */
final class SymfonySetList
{
    /**
     * @var string
     */
    public const CONFIGS = __DIR__ . '/../../config/sets/symfony/configs.php';
    /**
     * Rules bound to the exact Symfony package version they are available from
     * @var string
     */
    public const COMPOSER_BASED = __DIR__ . '/../../config/sets/symfony/composer-based.php';
    /**
     * @var string
     */
    public const SYMFONY_CODE_QUALITY = __DIR__ . '/../../config/sets/symfony/symfony-code-quality.php';
    /**
     * @var string
     */
    public const SYMFONY_CONSTRUCTOR_INJECTION = __DIR__ . '/../../config/sets/symfony/symfony-constructor-injection.php';
    /**
     * @internal Use ->withAttributesSets(symfony: true) in rector.php config instead
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/symfony/annotations-to-attributes.php';
}

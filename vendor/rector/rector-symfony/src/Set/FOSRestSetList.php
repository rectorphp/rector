<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

/**
 * @deprecated Use ->withAttributesSets(symfony: true) in rector.php config instead
 *
 * @api used in public
 */
final class FOSRestSetList
{
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/fosrest/annotations-to-attributes.php';
}

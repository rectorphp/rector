<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

/**
 * @deprecated Use ->withAttributesSets(symfony: true) in rector.php config instead
 *
 * @api
 */
final class JMSSetList
{
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/jms/annotations-to-attributes.php';
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

/**
 * @api use in RectorConfig class
 * @internal Do not use outside of Rector core. Might change any time.
 */
final class SymfonyInternalSetList
{
    /**
     * @var string
     */
    public const JMS_ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/jms/annotations-to-attributes.php';
    /**
     * @var string
     */
    public const FOS_REST_ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/fosrest/annotations-to-attributes.php';
    /**
     * @var string
     */
    public const SENSIOLABS_ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/sensiolabs/annotations-to-attributes.php';
}

<?php

declare (strict_types=1);
namespace Rector\Doctrine\Enum;

final class OdmMappingClass
{
    /**
     * @var string
     */
    public const DOCUMENT = 'Doctrine\ODM\MongoDB\Mapping\Annotations\Document';
    /**
     * @var string
     */
    public const REFERENCE_MANY = 'Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany';
    /**
     * @var string
     */
    public const REFERENCE_ONE = 'Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne';
    /**
     * @var string
     */
    public const EMBED_MANY = 'Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany';
    /**
     * @var string
     */
    public const EMBED_ONE = 'Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne';
}

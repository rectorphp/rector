<?php

declare (strict_types=1);
namespace Rector\Doctrine\Enum;

final class OdmMappingClass
{
    /**
     * @var string
     */
    public const DOCUMENT = 'Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\Document';
    /**
     * @var string
     */
    public const REFERENCE_MANY = 'Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\ReferenceMany';
    public const EMBED_MANY = 'Doctrine\\ODM\\MongoDB\\Mapping\\Annotations\\EmbedMany';
}

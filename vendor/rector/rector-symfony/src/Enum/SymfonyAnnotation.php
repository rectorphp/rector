<?php

declare (strict_types=1);
namespace Rector\Symfony\Enum;

final class SymfonyAnnotation
{
    /**
     * @var string
     */
    public const ROUTE = 'Symfony\\Component\\Routing\\Annotation\\Route';
    /**
     * @var string
     */
    public const TWIG_TEMPLATE = 'Symfony\\Bridge\\Twig\\Attribute\\Template';
    /**
     * @var string
     */
    public const MAP_ENTITY_CLASS = 'Symfony\\Bridge\\Doctrine\\Attribute\\MapEntity';
}

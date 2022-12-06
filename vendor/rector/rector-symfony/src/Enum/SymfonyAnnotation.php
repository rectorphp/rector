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
    public const SENSIO_METHOD = 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Method';
    /**
     * @var string
     */
    public const SENSIO_TEMPLATE = 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template';
    /**
     * @var string
     */
    public const TWIG_TEMPLATE = 'Symfony\\Bridge\\Twig\\Attribute\\Template';
}

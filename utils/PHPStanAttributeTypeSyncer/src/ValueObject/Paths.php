<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanAttributeTypeSyncer\ValueObject;

final class Paths
{
    /**
     * @var string
     */
    public const NAMESPACE_PHPDOC_NODE = 'Rector\AttributeAwarePhpDoc\Ast\PhpDoc';

    /**
     * @var string
     */
    public const NAMESPACE_TYPE_NODE = 'Rector\AttributeAwarePhpDoc\Ast\Type';

    /**
     * @var string
     */
    public const NAMESPACE_PHPDOC_NODE_FACTORY = 'Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\PhpDoc';

    /**
     * @var string
     */
    public const NAMESPACE_TYPE_NODE_FACTORY = 'Rector\AttributeAwarePhpDoc\AttributeAwareNodeFactory\Type';
}

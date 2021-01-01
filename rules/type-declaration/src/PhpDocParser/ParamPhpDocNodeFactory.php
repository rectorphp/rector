<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PhpDocParser;

use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\NodeNameResolver\NodeNameResolver;

final class ParamPhpDocNodeFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function create(TypeNode $typeNode, Param $param): AttributeAwareParamTagValueNode
    {
        return new AttributeAwareParamTagValueNode(
            $typeNode,
            $param->variadic,
            '$' . $this->nodeNameResolver->getName($param),
            ''
        );
    }
}

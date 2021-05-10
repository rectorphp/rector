<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PhpDocParser;

use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use Rector\NodeNameResolver\NodeNameResolver;

final class ParamPhpDocNodeFactory
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function create(TypeNode $typeNode, Param $param): VariadicAwareParamTagValueNode
    {
        return new VariadicAwareParamTagValueNode(
            $typeNode,
            $param->variadic,
            '$' . $this->nodeNameResolver->getName($param),
            ''
        );
    }
}

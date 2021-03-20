<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PhpDocParser;

use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
<<<<<<< HEAD
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
=======
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\VariadicAwareParamTagValueNode;
>>>>>>> e26c75d9e1... rename AttributeAwareParamTagValueNode to VariadicAwareParamTagValueNode
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

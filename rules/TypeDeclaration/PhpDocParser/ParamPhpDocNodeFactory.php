<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PhpDocParser;

use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\NodeNameResolver\NodeNameResolver;
final class ParamPhpDocNodeFactory
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function create(TypeNode $typeNode, Param $param) : ParamTagValueNode
    {
        return new ParamTagValueNode($typeNode, $param->variadic, '$' . $this->nodeNameResolver->getName($param), '', \false);
    }
}

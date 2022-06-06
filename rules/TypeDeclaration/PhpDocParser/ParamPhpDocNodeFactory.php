<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\PhpDocParser;

use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class ParamPhpDocNodeFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function create(TypeNode $typeNode, Param $param) : ParamTagValueNode
    {
        return new ParamTagValueNode($typeNode, $param->variadic, '$' . $this->nodeNameResolver->getName($param), '');
    }
}

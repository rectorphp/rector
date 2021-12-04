<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PhpDocParser;

use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use Rector\NodeNameResolver\NodeNameResolver;
final class ParamPhpDocNodeFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function create(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode, \PhpParser\Node\Param $param) : \Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode
    {
        return new \Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode($typeNode, $param->variadic, '$' . $this->nodeNameResolver->getName($param), '');
    }
}

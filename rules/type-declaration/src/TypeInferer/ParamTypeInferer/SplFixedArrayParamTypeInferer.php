<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use PhpParser\Node\Param;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;

final class SplFixedArrayParamTypeInferer implements ParamTypeInfererInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function inferParam(Param $param): Type
    {
        $paramType = $this->nodeTypeResolver->resolve($param->type);
        if ($paramType->isSuperTypeOf(new ObjectType('SplArrayFixed'))->no()) {
            return new MixedType();
        }

        if (! $paramType instanceof TypeWithClassName) {
            return new MixedType();
        }

        $types = [];
        if ($paramType->getClassName() === 'PhpCsFixer\Tokenizer\Tokens') {
            $types[] = new ObjectType('PhpCsFixer\Tokenizer\Token');
        }

        return new GenericObjectType($paramType->getClassName(), $types);
    }
}

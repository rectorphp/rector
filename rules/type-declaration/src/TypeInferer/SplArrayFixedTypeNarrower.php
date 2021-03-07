<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class SplArrayFixedTypeNarrower
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function narrow(\PHPStan\Type\Type $paramType): Type
    {
        if ($paramType->isSuperTypeOf(new ObjectType('SplArrayFixed'))->no()) {
            return $paramType;
        }

        if (! $paramType instanceof TypeWithClassName) {
            return $paramType;
        }

        if ($paramType instanceof GenericObjectType) {
            return $paramType;
        }

        $types = [];

        if ($paramType->getClassName() === 'PhpCsFixer\Tokenizer\Tokens') {
            $types[] = new ObjectType('PhpCsFixer\Tokenizer\Token');
        }

        if ($paramType->getClassName() === 'PhpCsFixer\Doctrine\Annotation\Tokens') {
            $types[] = new ObjectType('PhpCsFixer\Doctrine\Annotation\Token');
        }

        if ($types === []) {
            return $paramType;
        }

        return new GenericObjectType($paramType->getClassName(), $types);
    }
}

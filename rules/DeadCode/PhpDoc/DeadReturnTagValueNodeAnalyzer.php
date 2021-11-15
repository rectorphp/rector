<?php

declare(strict_types=1);

namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;

final class DeadReturnTagValueNodeAnalyzer
{
    public function __construct(
        private TypeComparator $typeComparator,
        private BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function isDead(ReturnTagValueNode $returnTagValueNode, FunctionLike $functionLike): bool
    {
        $returnType = $functionLike->getReturnType();
        if ($returnType === null) {
            return false;
        }

        $classLike = $this->betterNodeFinder->findParentType($functionLike, ClassLike::class);
        if ($classLike instanceof Trait_ && $returnTagValueNode->type instanceof ThisTypeNode) {
            return false;
        }

        if (! $this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual(
            $returnType,
            $returnTagValueNode->type,
            $functionLike,
        )) {
            return false;
        }

        if (in_array($returnTagValueNode->type::class, [
            GenericTypeNode::class,
            SpacingAwareCallableTypeNode::class,
        ], true)) {
            return false;
        }

        if (! $returnTagValueNode->type instanceof BracketsAwareUnionTypeNode) {
            return $returnTagValueNode->description === '';
        }

        if (! $this->hasGenericType($returnTagValueNode->type)) {
            return $returnTagValueNode->description === '';
        }

        return false;
    }

    private function hasGenericType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode): bool
    {
        $types = $bracketsAwareUnionTypeNode->types;

        foreach ($types as $type) {
            if ($type instanceof GenericTypeNode) {
                if ($type->type instanceof IdentifierTypeNode && $type->type->name === 'array') {
                    continue;
                }

                return true;
            }
        }

        return false;
    }
}

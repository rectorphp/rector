<?php

declare(strict_types=1);

namespace Rector\SimplePhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;

final class PhpDocNodeTraverser
{
    public function traverseWithCallable(PhpDocNode $phpDocNode, string $docContent, callable $callable): void
    {
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            $phpDocChildNode = $callable($phpDocChildNode);
            $phpDocNode->children[$key] = $phpDocChildNode;

            if ($phpDocChildNode instanceof PhpDocTextNode) {
                continue;
            }

            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            $phpDocChildNode->value = $callable($phpDocChildNode->value, $docContent);

            if ($this->isValueNodeWithType($phpDocChildNode->value)) {
                /** @var ParamTagValueNode|VarTagValueNode|ReturnTagValueNode|GenericTypeNode $valueNode */
                $valueNode = $phpDocChildNode->value;

                $valueNode->type = $this->traverseTypeNode($valueNode->type, $docContent, $callable);
            }
        }
    }

    private function isValueNodeWithType(PhpDocTagValueNode $phpDocTagValueNode): bool
    {
        return $phpDocTagValueNode instanceof PropertyTagValueNode ||
            $phpDocTagValueNode instanceof ReturnTagValueNode ||
            $phpDocTagValueNode instanceof ParamTagValueNode ||
            $phpDocTagValueNode instanceof VarTagValueNode ||
            $phpDocTagValueNode instanceof ThrowsTagValueNode;
    }

    private function traverseTypeNode(TypeNode $typeNode, string $docContent, callable $callable): TypeNode
    {
        $typeNode = $callable($typeNode, $docContent);

        if ($typeNode instanceof ArrayTypeNode || $typeNode instanceof NullableTypeNode || $typeNode instanceof GenericTypeNode) {
            $typeNode->type = $this->traverseTypeNode($typeNode->type, $docContent, $callable);
        }

        if ($typeNode instanceof GenericTypeNode) {
            foreach ($typeNode->genericTypes as $key => $genericType) {
                $typeNode->genericTypes[$key] = $this->traverseTypeNode($genericType, $docContent, $callable);
            }
        }

        if ($typeNode instanceof UnionTypeNode || $typeNode instanceof IntersectionTypeNode) {
            foreach ($typeNode->types as $key => $subTypeNode) {
                $typeNode->types[$key] = $this->traverseTypeNode($subTypeNode, $docContent, $callable);
            }
        }

        return $typeNode;
    }
}

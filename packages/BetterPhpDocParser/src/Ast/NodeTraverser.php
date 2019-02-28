<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Ast;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;

final class NodeTraverser
{
    public function traverseWithCallable(PhpDocNode $phpDocNode, callable $callable): void
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

            $phpDocChildNode->value = $callable($phpDocChildNode->value);

            if ($this->isValueNodeWithType($phpDocChildNode->value)) {
                /** @var ParamTagValueNode|VarTagValueNode|ReturnTagValueNode $valueNode */
                $valueNode = $phpDocChildNode->value;

                $valueNode->type = $this->traverseTypeNode($valueNode->type, $callable);
            }
        }
    }

    private function isValueNodeWithType(PhpDocTagValueNode $phpDocTagValueNode): bool
    {
        return $phpDocTagValueNode instanceof ReturnTagValueNode ||
            $phpDocTagValueNode instanceof ParamTagValueNode ||
            $phpDocTagValueNode instanceof VarTagValueNode;
    }

    private function traverseTypeNode(TypeNode $typeNode, callable $callable): TypeNode
    {
        $typeNode = $callable($typeNode);

        if ($typeNode instanceof ArrayTypeNode) {
            $typeNode->type = $this->traverseTypeNode($typeNode->type, $callable);
        }

        if ($typeNode instanceof UnionTypeNode || $typeNode instanceof IntersectionTypeNode) {
            foreach ($typeNode->types as $key => $subTypeNode) {
                $typeNode->types[$key] = $this->traverseTypeNode($subTypeNode, $callable);
            }
        }

        if ($typeNode instanceof NullableTypeNode) {
            $typeNode->type = $this->traverseTypeNode($typeNode->type, $callable);
        }

        return $typeNode;
    }
}

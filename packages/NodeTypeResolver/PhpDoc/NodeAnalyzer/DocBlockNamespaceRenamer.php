<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Naming\NamespaceMatcher;
use Rector\Renaming\ValueObject\RenamedNamespace;

final class DocBlockNamespaceRenamer
{
    /**
     * @var array<class-string<PhpDocTagValueNode>>
     */
    private const TO_BE_CHANGED = [
        ReturnTagValueNode::class,
        VariadicAwareParamTagValueNode::class,
        VarTagValueNode::class,
    ];

    public function __construct(
        private readonly NamespaceMatcher $namespaceMatcher,
        private readonly PhpDocInfoFactory $phpDocInfoFactory
    ) {
    }

    /**
     * @param array<string, string> $oldToNewNamespaces
     */
    public function renameFullyQualifiedNamespace(
        Property|ClassMethod|Function_|Expression|ClassLike|FileWithoutNamespace $node,
        array $oldToNewNamespaces
    ): ?Node {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $children = $phpDocNode->children;

        foreach ($children as $child) {
            if (! $child instanceof PhpDocTagNode) {
                continue;
            }

            $value = $child->value;
            if (! in_array($value::class, self::TO_BE_CHANGED, true)) {
                continue;
            }

            /** @var ReturnTagValueNode|VariadicAwareParamTagValueNode|VarTagValueNode $value */
            $this->refactorDocblock($value, $oldToNewNamespaces);
        }

        if (! $phpDocInfo->hasChanged()) {
            return null;
        }

        return $node;
    }

    /**
     * @param array<string, string> $oldToNewNamespaces
     */
    private function refactorDocblock(
        ReturnTagValueNode|VariadicAwareParamTagValueNode|VarTagValueNode $value,
        array $oldToNewNamespaces
    ): void {
        /** @var ReturnTagValueNode|VariadicAwareParamTagValueNode|VarTagValueNode $value */
        $types = $value->type instanceof BracketsAwareUnionTypeNode
            ? $value->type->types
            : [$value->type];

        foreach ($types as $key => $type) {
            if (! $type instanceof IdentifierTypeNode) {
                continue;
            }

            $name = $type->name;
            $trimmedName = ltrim($type->name, '\\');

            if ($name === $trimmedName) {
                continue;
            }

            $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace(
                $trimmedName,
                $oldToNewNamespaces
            );
            if (! $renamedNamespaceValueObject instanceof RenamedNamespace) {
                continue;
            }

            $newType = new IdentifierTypeNode('\\' . $renamedNamespaceValueObject->getNameInNewNamespace());

            if ($value->type instanceof BracketsAwareUnionTypeNode) {
                $types[$key] = $newType;
            } else {
                $value->type = $newType;
            }
        }

        if ($value->type instanceof BracketsAwareUnionTypeNode) {
            $value->type->types = $types;
        }
    }
}

<?php

declare (strict_types=1);
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
    private const TO_BE_CHANGED = [\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class, \Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode::class, \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class];
    /**
     * @readonly
     * @var \Rector\Naming\NamespaceMatcher
     */
    private $namespaceMatcher;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\Naming\NamespaceMatcher $namespaceMatcher, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->namespaceMatcher = $namespaceMatcher;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @param array<string, string> $oldToNewNamespaces
     * @param \PhpParser\Node\Stmt\ClassLike|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Property|\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $node
     */
    public function renameFullyQualifiedNamespace($node, array $oldToNewNamespaces) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $children = $phpDocNode->children;
        foreach ($children as $child) {
            if (!$child instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                continue;
            }
            $value = $child->value;
            if (!\in_array(\get_class($value), self::TO_BE_CHANGED, \true)) {
                continue;
            }
            /** @var ReturnTagValueNode|VariadicAwareParamTagValueNode|VarTagValueNode $value */
            $this->refactorDocblock($value, $oldToNewNamespaces);
        }
        if (!$phpDocInfo->hasChanged()) {
            return null;
        }
        return $node;
    }
    /**
     * @param array<string, string> $oldToNewNamespaces
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode|\Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode $value
     */
    private function refactorDocblock($value, array $oldToNewNamespaces) : void
    {
        /** @var ReturnTagValueNode|VariadicAwareParamTagValueNode|VarTagValueNode $value */
        $types = $value->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode ? $value->type->types : [$value->type];
        foreach ($types as $key => $type) {
            if (!$type instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
                continue;
            }
            $name = $type->name;
            $trimmedName = \ltrim($type->name, '\\');
            if ($name === $trimmedName) {
                continue;
            }
            $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace($trimmedName, $oldToNewNamespaces);
            if (!$renamedNamespaceValueObject instanceof \Rector\Renaming\ValueObject\RenamedNamespace) {
                continue;
            }
            $newType = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('\\' . $renamedNamespaceValueObject->getNameInNewNamespace());
            if ($value->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
                $types[$key] = $newType;
            } else {
                $value->type = $newType;
            }
        }
        if ($value->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
            $value->type->types = $types;
        }
    }
}

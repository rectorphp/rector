<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VarAnnotationManipulator
{
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory,
        private PhpDocTypeChanger $phpDocTypeChanger
    ) {
    }

    public function decorateNodeWithInlineVarType(
        Node $node,
        TypeWithClassName $typeWithClassName,
        string $variableName
    ): void {
        $phpDocInfo = $this->resolvePhpDocInfo($node);

        // already done
        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return;
        }

        $fullyQualifiedIdentifierTypeNode = new FullyQualifiedIdentifierTypeNode($typeWithClassName->getClassName());

        $varTagValueNode = new VarTagValueNode($fullyQualifiedIdentifierTypeNode, '$' . $variableName, '');
        $phpDocInfo->addTagValueNode($varTagValueNode);
        $phpDocInfo->makeSingleLined();
    }

    public function decorateNodeWithType(Node $node, Type $staticType): void
    {
        if ($staticType instanceof MixedType) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $staticType);
    }

    private function resolvePhpDocInfo(Node $node): PhpDocInfo
    {
        $currentStmt = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($currentStmt instanceof Expression) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($currentStmt);
        } else {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        }

        $phpDocInfo->makeSingleLined();

        return $phpDocInfo;
    }
}

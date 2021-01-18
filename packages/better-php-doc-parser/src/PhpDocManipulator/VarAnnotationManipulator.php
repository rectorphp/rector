<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\FullyQualifiedIdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VarAnnotationManipulator
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
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

        $attributeAwareVarTagValueNode = new AttributeAwareVarTagValueNode(
            $fullyQualifiedIdentifierTypeNode,
            '$' . $variableName,
            ''
        );

        $phpDocInfo->addTagValueNode($attributeAwareVarTagValueNode);
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

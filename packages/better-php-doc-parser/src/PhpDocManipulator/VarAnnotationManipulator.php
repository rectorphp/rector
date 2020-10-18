<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareFullyQualifiedIdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class VarAnnotationManipulator
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
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

        $attributeAwareFullyQualifiedIdentifierTypeNode = new AttributeAwareFullyQualifiedIdentifierTypeNode(
            $typeWithClassName->getClassName()
        );

        $attributeAwareVarTagValueNode = new AttributeAwareVarTagValueNode(
            $attributeAwareFullyQualifiedIdentifierTypeNode,
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

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
        }

        $phpDocInfo->changeVarType($staticType);
    }

    private function resolvePhpDocInfo(Node $node): PhpDocInfo
    {
        $currentStmt = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($currentStmt instanceof Expression) {
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $currentStmt->getAttribute(AttributeKey::PHP_DOC_INFO);
        } else {
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        }

        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
        }

        $phpDocInfo->makeSingleLined();

        return $phpDocInfo;
    }
}

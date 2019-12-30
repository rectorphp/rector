<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Type\MixedType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;
use Rector\TypeDeclaration\VendorLock\VendorLockResolver;
use Rector\ValueObject\PhpVersionFeature;

/**
 * @source https://wiki.php.net/rfc/typed_properties_v2#proposal
 *
 * @see \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\TypedPropertyRectorTest
 */
final class TypedPropertyRector extends AbstractRector
{
    /**
     * @var PropertyTypeInferer
     */
    private $propertyTypeInferer;

    /**
     * @var VendorLockResolver
     */
    private $vendorLockResolver;

    public function __construct(PropertyTypeInferer $propertyTypeInferer, VendorLockResolver $vendorLockResolver)
    {
        $this->propertyTypeInferer = $propertyTypeInferer;
        $this->vendorLockResolver = $vendorLockResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes property `@var` annotations from annotation to type.',
            [
                new CodeSample(
                    <<<'PHP'
final class SomeClass
{
    /**
     * @var int
     */
    private count;
}
PHP
                    ,
                    <<<'PHP'
final class SomeClass
{
    private int count;
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }

        // type is already set → skip
        if ($node->type !== null) {
            return null;
        }

        $varType = $this->propertyTypeInferer->inferProperty($node);
        if ($varType instanceof MixedType) {
            return null;
        }

        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, 'property');
        if ($propertyTypeNode === null) {
            return null;
        }

        if ($this->vendorLockResolver->isPropertyChangeVendorLockedIn($node)) {
            return null;
        }

        $this->removeVarPhpTagValueNodeIfNotComment($node);

        $node->type = $propertyTypeNode;

        return $node;
    }

    private function removeVarPhpTagValueNodeIfNotComment(Property $property): void
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        // nothing to remove
        if ($propertyPhpDocInfo === null) {
            return;
        }

        $varTagValueNode = $propertyPhpDocInfo->getByType(VarTagValueNode::class);
        if ($varTagValueNode === null) {
            return;
        }

        // has description? keep it
        if ($varTagValueNode->description !== '') {
            return;
        }

        // keep generic types
        if ($varTagValueNode->type instanceof GenericTypeNode) {
            return;
        }

        // keep string[] etc.
        if ($this->isNonBasicArrayType($property, $varTagValueNode)) {
            return;
        }

        $propertyPhpDocInfo->removeByType(VarTagValueNode::class);
        $this->docBlockManipulator->updateNodeWithPhpDocInfo($property, $propertyPhpDocInfo);
    }

    private function isNonBasicArrayType(Property $property, VarTagValueNode $varTagValueNode): bool
    {
        if (! $this->isArrayTypeNode($varTagValueNode)) {
            return false;
        }

        $varTypeDocString = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPhpDocString(
            $varTagValueNode->type,
            $property
        );

        return $varTypeDocString !== 'array';
    }

    private function isArrayTypeNode(VarTagValueNode $varTagValueNode): bool
    {
        return $varTagValueNode->type instanceof ArrayTypeNode;
    }
}

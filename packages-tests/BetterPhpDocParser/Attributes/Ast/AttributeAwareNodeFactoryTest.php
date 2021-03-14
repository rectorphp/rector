<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class AttributeAwareNodeFactoryTest extends AbstractKernelTestCase
{
    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->attributeAwareNodeFactory = $this->getService(AttributeAwareNodeFactory::class);
    }

    public function testPhpDocNodeAndChildren(): void
    {
        $phpDocNode = $this->createSomeTextDocNode();

        $attributeAwarePhpDocNode = $this->attributeAwareNodeFactory->createFromNode($phpDocNode, '');
        $this->assertInstanceOf(PhpDocNode::class, $attributeAwarePhpDocNode);
        $this->assertInstanceOf(AttributeAwarePhpDocNode::class, $attributeAwarePhpDocNode);

        $childNode = $attributeAwarePhpDocNode->children[0];

        $this->assertInstanceOf(PhpDocTextNode::class, $childNode);
    }

    public function testPropertyTag(): void
    {
        $phpDocNode = $this->createPropertyDocNode();

        $attributeAwarePhpDocNode = $this->attributeAwareNodeFactory->createFromNode($phpDocNode, '');

        $childNode = $attributeAwarePhpDocNode->children[0];
        $this->assertInstanceOf(PhpDocTagNode::class, $childNode);

        // test param tag
        /** @var PhpDocTagNode $childNode */
        $propertyTagValueNode = $childNode->value;
        $this->assertInstanceOf(PropertyTagValueNode::class, $propertyTagValueNode);

        // test nullable
        /** @var PropertyTagValueNode $propertyTagValueNode */
        $nullableTypeNode = $propertyTagValueNode->type;

        $this->assertInstanceOf(NullableTypeNode::class, $nullableTypeNode);

        // test type inside nullable
        /** @var NullableTypeNode $nullableTypeNode */
        $identifierTypeNode = $nullableTypeNode->type;
        $this->assertInstanceOf(IdentifierTypeNode::class, $identifierTypeNode);
    }

    /**
     * Creates doc block for:
     * some text
     */
    private function createSomeTextDocNode(): PhpDocNode
    {
        return new PhpDocNode([new PhpDocTextNode('some text')]);
    }

    /**
     * Creates doc block for:
     * @property string|null $name
     */
    private function createPropertyDocNode(): PhpDocNode
    {
        $nullableTypeNode = new NullableTypeNode(new IdentifierTypeNode('string'));
        $propertyTagValueNode = new PropertyTagValueNode($nullableTypeNode, 'name', '');

        $children = [new PhpDocTagNode('@property', $propertyTagValueNode)];

        return new PhpDocNode($children);
    }
}

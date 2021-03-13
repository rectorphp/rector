<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\DecoratingNodeFactory;
use Rector\AttributeAwarePhpDoc\ValueObject\Type\BetterArrayTypeNode;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class DecoratingNodeFactoryTest extends AbstractKernelTestCase
{
    /**
     * @var DecoratingNodeFactory
     */
    private $decoratingNodeFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->decoratingNodeFactory = $this->getService(DecoratingNodeFactory::class);
    }

    public function testPhpDocNodeAndChildren(): void
    {
        $phpDocNode = $this->createSomeTextDocNode();

        $phpDocNode = $this->decoratingNodeFactory->createFromNode($phpDocNode, '');
        $this->assertInstanceOf(PhpDocNode::class, $phpDocNode);

        /** @var PhpDocNode $phpDocNode */
        $childNode = $phpDocNode->children[0];
        $this->assertInstanceOf(PhpDocTextNode::class, $childNode);
    }

    public function testTagNode(): void
    {
        $phpDocNode = $this->createPropertyDocNode();

        /** @var PhpDocNode $phpDocNode */
        $phpDocNode = $this->decoratingNodeFactory->createFromNode($phpDocNode, '');

        $childNode = $phpDocNode->children[0];
        $this->assertInstanceOf(PhpDocTagNode::class, $childNode);

        // test param tag
        /** @var PhpDocTagNode $childNode */
        $propertyTagValueNode = $childNode->value;
        $this->assertInstanceOf(PropertyTagValueNode::class, $propertyTagValueNode);
    }

    public function testType(): void
    {
        $phpDocNode = $this->createPropertyDocNode();

        /** @var PhpDocNode $phpDocNode */
        $phpDocNode = $this->decoratingNodeFactory->createFromNode($phpDocNode, '');
        $childNode = $phpDocNode->children[0];
        $propertyTagValueNode = $childNode->value;

        /** @var PropertyTagValueNode $propertyTagValueNode */
        $typeNode = $propertyTagValueNode->type;

        $this->assertInstanceOf(ArrayTypeNode::class, $typeNode);
        $this->assertInstanceOf(BetterArrayTypeNode::class, $typeNode);
    }

    public function testAlreadyAttributeAware(): void
    {
        $PhpDocNode = new PhpDocNode([]);
        $returnedNode = $this->decoratingNodeFactory->createFromNode($PhpDocNode, '');

        $this->assertSame($returnedNode, $PhpDocNode);
    }

    /**
     * Creates doc block for:
     * some text
     */
    private function createSomeTextDocNode(): PhpDocNode
    {
        return new PhpDocNode([new PhpDocTextNode('some text')]);
    }

    private function createPropertyDocNode(): PhpDocNode
    {
        $arrayTypeNode = new ArrayTypeNode(new IdentifierTypeNode('string'));
        $propertyTagValueNode = new PropertyTagValueNode($arrayTypeNode, 'name', '');

        $children = [new PhpDocTagNode('@property', $propertyTagValueNode)];
        return new PhpDocNode($children);
    }
}

<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\Attributes\Ast;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\VariadicAwareParamTagValueNode;
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

    public function testPropertyTag(): void
    {
        $phpDocNode = $this->createParamDocNode();

        $reprintedPhpDocNode = $this->attributeAwareNodeFactory->transform($phpDocNode, '');

        $childNode = $reprintedPhpDocNode->children[0];
        $this->assertInstanceOf(PhpDocTagNode::class, $childNode);

        // test param tag
        /** @var PhpDocTagNode $childNode */
        $propertyTagValueNode = $childNode->value;
        $this->assertInstanceOf(VariadicAwareParamTagValueNode::class, $propertyTagValueNode);
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
    private function createParamDocNode(): PhpDocNode
    {
        $nullableTypeNode = new NullableTypeNode(new IdentifierTypeNode('string'));
        $paramTagValueNode = new ParamTagValueNode($nullableTypeNode, true, 'name', '');

        $children = [new PhpDocTagNode('@param', $paramTagValueNode)];

        return new PhpDocNode($children);
    }
}

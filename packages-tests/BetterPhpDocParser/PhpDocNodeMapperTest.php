<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use Rector\BetterPhpDocParser\PhpDocNodeMapper;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\VariadicAwareParamTagValueNode;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class PhpDocNodeMapperTest extends AbstractTestCase
{
    private PhpDocNodeMapper $phpDocNodeMapper;

    protected function setUp(): void
    {
        $this->boot();
        $this->phpDocNodeMapper = $this->getService(PhpDocNodeMapper::class);
    }

    public function testParamTag(): void
    {
        $phpDocNode = $this->createParamDocNode();

        $this->phpDocNodeMapper->transform($phpDocNode, new BetterTokenIterator([]));

        $childNode = $phpDocNode->children[0];
        $this->assertInstanceOf(PhpDocTagNode::class, $childNode);

        // test param tag
        /** @var PhpDocTagNode $childNode */
        $propertyTagValueNode = $childNode->value;
        $this->assertInstanceOf(VariadicAwareParamTagValueNode::class, $propertyTagValueNode);
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

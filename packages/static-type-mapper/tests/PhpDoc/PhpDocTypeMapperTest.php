<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\Tests\PhpDoc;

use Iterator;
use PhpParser\Node\Stmt\Nop;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
use Rector\StaticTypeMapper\PhpDoc\PhpDocTypeMapper;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class PhpDocTypeMapperTest extends AbstractKernelTestCase
{
    /**
     * @var PhpDocTypeMapper
     */
    private $phpDocTypeMapper;

    /**
     * @var NameScopeFactory
     */
    private $nameScopeFactory;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->phpDocTypeMapper = $this->getService(PhpDocTypeMapper::class);
        $this->nameScopeFactory = $this->getService(NameScopeFactory::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(TypeNode $typeNode, string $expectedPHPStanType): void
    {
        $nop = new Nop();
        $nameScope = $this->nameScopeFactory->createNameScopeFromNode($nop);

        $phpStanType = $this->phpDocTypeMapper->mapToPHPStanType($typeNode, $nop, $nameScope);

        $this->assertInstanceOf($expectedPHPStanType, $phpStanType);
    }

    public function provideData(): Iterator
    {
        $arrayShapeNode = new ArrayShapeNode([new ArrayShapeItemNode(null, true, new IdentifierTypeNode('string'))]);

        yield [$arrayShapeNode, ArrayType::class];
    }
}

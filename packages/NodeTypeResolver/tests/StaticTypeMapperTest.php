<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use Iterator;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class StaticTypeMapperTest extends AbstractKernelTestCase
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->staticTypeMapper = self::$container->get(StaticTypeMapper::class);
    }

    /**
     * @dataProvider provideDataForTestMapPHPStanTypeToStrings()
     * @param string[] $expectedStrings
     */
    public function testMapPHPStanTypeToStrings(Type $type, array $expectedStrings): void
    {
        $this->assertSame($expectedStrings, $this->staticTypeMapper->mapPHPStanTypeToStrings($type));
    }

    public function provideDataForTestMapPHPStanTypeToStrings(): Iterator
    {
        $constantArrayType = new ConstantArrayType([], [new ConstantStringType('a'), new ConstantStringType('b')]);

        yield [$constantArrayType, ['string[]']];
    }
}

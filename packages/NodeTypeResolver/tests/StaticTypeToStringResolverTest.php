<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests;

use Iterator;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\StaticTypeToStringResolver;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class StaticTypeToStringResolverTest extends AbstractKernelTestCase
{
    /**
     * @var StaticTypeToStringResolver
     */
    private $staticTypeToStringResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->staticTypeToStringResolver = self::$container->get(StaticTypeToStringResolver::class);
    }

    /**
     * @dataProvider provideStaticTypesToStrings()
     */
    public function test(Type $type, array $expectedStrings): void
    {
        $this->assertSame($expectedStrings, $this->staticTypeToStringResolver->resolveObjectType($type));
    }

    public function provideStaticTypesToStrings(): Iterator
    {
        $constantArrayType = new ConstantArrayType([], [new ConstantStringType('a'), new ConstantStringType('b')]);

        yield [$constantArrayType, ['string[]']];
    }
}

<?php declare(strict_types=1);

namespace Rector\Tests\PhpParser\Node\Value;

use Iterator;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Name\FullyQualified;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Value\ValueResolver;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class ValueResolverTest extends AbstractKernelTestCase
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->valueResolver = self::$container->get(ValueResolver::class);
    }

    /**
     * @dataProvider dataProvider
     * @param mixed $expected
     */
    public function test($expected, Expr $expr): void
    {
        $this->assertSame($expected, $this->valueResolver->resolve($expr));
    }

    public function dataProvider(): Iterator
    {
        $builderFactory = new BuilderFactory();

        $classConstFetchNode = $builderFactory->classConstFetch('SomeClass', 'SOME_CONSTANT');
        $classConstFetchNode->class->setAttribute(
            AttributeKey::RESOLVED_NAME,
            new FullyQualified('SomeClassResolveName')
        );

        yield ['SomeClassResolveName::SOME_CONSTANT', $classConstFetchNode];
        yield [true, $builderFactory->val(true)];
        yield [1, $builderFactory->val(1)];
        yield [1.0, $builderFactory->val(1.0)];
        yield [null, $builderFactory->var('foo')];
        yield [2, new Plus($builderFactory->val(1), $builderFactory->val(1))];
        yield [null, new Plus($builderFactory->val(1), $builderFactory->var('foo'))];
    }
}

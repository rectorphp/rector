<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Node\Value;

use Iterator;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
     * @param mixed $expected
     * @dataProvider dataProvider
     */
    public function test($expected, Expr $expr): void
    {
        $this->assertSame($expected, $this->valueResolver->getValue($expr));
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

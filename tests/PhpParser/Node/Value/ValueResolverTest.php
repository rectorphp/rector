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
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ValueResolverTest extends AbstractKernelTestCase
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->valueResolver = $this->getService(ValueResolver::class);
    }

    /**
     * @param mixed $expectedValue
     * @dataProvider dataProvider
     */
    public function test(Expr $expr, $expectedValue): void
    {
        $resolvedValue = $this->valueResolver->getValue($expr);
        $this->assertSame($expectedValue, $resolvedValue);
    }

    public function dataProvider(): Iterator
    {
        $builderFactory = new BuilderFactory();

        $classConstFetchNode = $builderFactory->classConstFetch('SomeClass', 'SOME_CONSTANT');
        $classConstFetchNode->class->setAttribute(
            AttributeKey::RESOLVED_NAME,
            new FullyQualified('SomeClassResolveName')
        );

        yield [$classConstFetchNode, 'SomeClassResolveName::SOME_CONSTANT'];
        yield [$builderFactory->val(true), true];
        yield [$builderFactory->val(1), 1];
        yield [$builderFactory->val(1.0), 1.0];
        yield [$builderFactory->var('foo'), null];
        yield [new Plus($builderFactory->val(1), $builderFactory->val(1)), 2];
        yield [new Plus($builderFactory->val(1), $builderFactory->var('foo')), null];
    }
}

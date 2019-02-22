<?php declare(strict_types=1);

namespace Rector\Tests\PhpParser\Node\Value;

use PhpParser\BuilderFactory;
use PhpParser\Node\Name\FullyQualified;
use Rector\HttpKernel\RectorKernel;
use Rector\NodeTypeResolver\Node\Attribute;
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

    public function test(): void
    {
        $classConstFetchNode = (new BuilderFactory())->classConstFetch('SomeClass', 'SOME_CONSTANT');
        $classConstFetchNode->class->setAttribute(Attribute::RESOLVED_NAME, new FullyQualified('SomeClassResolveName'));

        $this->assertSame(
            'SomeClassResolveName::SOME_CONSTANT',
            $this->valueResolver->resolve($classConstFetchNode)
        );
    }
}

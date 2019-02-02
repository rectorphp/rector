<?php declare(strict_types=1);

namespace Rector\Tests\PhpParser\Node\Value;

use PhpParser\BuilderFactory;
use PhpParser\Node\Name\FullyQualified;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Tests\AbstractContainerAwareTestCase;

final class ValueResolverTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    protected function setUp(): void
    {
        $this->valueResolver = $this->container->get(ValueResolver::class);
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

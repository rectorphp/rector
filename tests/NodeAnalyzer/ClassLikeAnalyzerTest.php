<?php declare(strict_types=1);

namespace Rector\Tests\NodeAnalyzer;

use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\Tests\AbstractContainerAwareTestCase;

final class ClassLikeAnalyzerTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ClassLikeAnalyzer
     */
    private $classLikeAnalyzer;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    protected function setUp(): void
    {
        $this->classLikeAnalyzer = $this->container->get(ClassLikeAnalyzer::class);
        $this->builderFactory = $this->container->get(BuilderFactory::class);
    }

    public function testAnonymousClass(): void
    {
        $this->assertSame([], $this->classLikeAnalyzer->resolveTypeAndParentTypes(new Class_(null)));

        $classWithParent = new Class_(null);
        $classWithParent->extends = new Name('SomeParentClass');

        $this->assertSame(
            ['SomeParentClass'],
            $this->classLikeAnalyzer->resolveTypeAndParentTypes($classWithParent)
        );

        $classWithParent->implements[] = new Name('SomeInterface');

        $this->assertSame(
            ['SomeParentClass', 'SomeInterface'],
            $this->classLikeAnalyzer->resolveTypeAndParentTypes($classWithParent)
        );
    }
}

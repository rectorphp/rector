<?php declare(strict_types=1);

namespace Rector\Tests\NodeAnalyzer;

use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
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

    public function testClass(): void
    {
        $this->assertSame(
            ['SomeClass'],
            $this->classLikeAnalyzer->resolveTypeAndParentTypes(new Class_('SomeClass'))
        );
    }

    public function testClassWithParentClass(): void
    {
        $classWithParent = $this->builderFactory->class('AnotherSomeClass')
            ->extend('AnotherParentClass')
            ->getNode();

        $this->assertSame(
            ['AnotherSomeClass', 'AnotherParentClass'],
            $this->classLikeAnalyzer->resolveTypeAndParentTypes($classWithParent)
        );
    }

    public function testInterface(): void
    {
        $this->assertSame(
            ['SomeInterface'],
            $this->classLikeAnalyzer->resolveTypeAndParentTypes(new Interface_('SomeInterface'))
        );
    }

    public function testInterfaceWithParentInterface(): void
    {
        $interfaceWithParent = $this->builderFactory->interface('SomeInterface')
            ->extend('AnotherInterface')
            ->getNode();

        $this->assertSame(
            ['SomeInterface', 'AnotherInterface'],
            $this->classLikeAnalyzer->resolveTypeAndParentTypes($interfaceWithParent)
        );
    }

    public function testTrait(): void
    {
        $this->assertSame(
            ['SomeTrait'],
            $this->classLikeAnalyzer->resolveTypeAndParentTypes(new Trait_('SomeTrait'))
        );
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

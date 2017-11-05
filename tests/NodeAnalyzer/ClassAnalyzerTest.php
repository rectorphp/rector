<?php declare(strict_types=1);

namespace Rector\Tests\NodeAnalyzer;

use PhpParser\BuilderFactory;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\Tests\AbstractContainerAwareTestCase;

final class ClassAnalyzerTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    protected function setUp(): void
    {
        $this->classAnalyzer = $this->container->get(ClassAnalyzer::class);
        $this->builderFactory = $this->container->get(BuilderFactory::class);
    }

    public function testClass(): void
    {
        $this->assertSame(
            ['SomeClass'],
            $this->classAnalyzer->resolveTypeAndParentTypes(new Class_('SomeClass'))
        );

        $classWithParent = $this->builderFactory->class('SomeClass')
            ->extend('ParentClass')
            ->getNode();

        $this->assertSame(
            ['SomeClass', 'ParentClass'],
            $this->classAnalyzer->resolveTypeAndParentTypes($classWithParent)
        );
    }

    public function testInterface(): void
    {
        $this->assertSame(
            ['SomeInterface'],
            $this->classAnalyzer->resolveTypeAndParentTypes(new Interface_('SomeInterface'))
        );

        $interfaceWithParent = $this->builderFactory->interface('SomeInterface')
            ->extend('AnotherInterface')
            ->getNode();

        $this->assertSame(
            ['SomeInterface', 'AnotherInterface'],
            $this->classAnalyzer->resolveTypeAndParentTypes($interfaceWithParent)
        );
    }

    public function testTrait(): void
    {
        $this->assertSame(
            ['SomeTrait'],
            $this->classAnalyzer->resolveTypeAndParentTypes(new Trait_('SomeTrait'))
        );
    }

    public function testAnonymousClass(): void
    {
        $this->assertSame(
            [],
            $this->classAnalyzer->resolveTypeAndParentTypes(new Class_(null))
        );

        $classWithParent = new Class_(null);
        $classWithParent->extends = new Name('SomeParentClass');

        $this->assertSame(
            ['SomeParentClass'],
            $this->classAnalyzer->resolveTypeAndParentTypes($classWithParent)
        );

        $classWithParent->implements[] = new Name('SomeInterface');

        $this->assertSame(
            ['SomeParentClass', 'SomeInterface'],
            $this->classAnalyzer->resolveTypeAndParentTypes($classWithParent)
        );
    }
}

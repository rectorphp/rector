<?php declare(strict_types=1);

namespace Rector\Tests\NodeAnalyzer;

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

    protected function setUp(): void
    {
        $this->classAnalyzer = $this->container->get(ClassAnalyzer::class);
    }

    public function testTraitResolveTypeAndParentTypes(): void
    {
        $this->assertSame(
            ['SomeClass'],
            $this->classAnalyzer->resolveTypeAndParentTypes(new Class_('SomeClass'))
        );

        $this->assertSame(
            ['SomeInterface'],
            $this->classAnalyzer->resolveTypeAndParentTypes(new Interface_('SomeInterface'))
        );

        $this->assertSame(
            ['SomeTrait'],
            $this->classAnalyzer->resolveTypeAndParentTypes(new Trait_('SomeTrait'))
        );
    }
}

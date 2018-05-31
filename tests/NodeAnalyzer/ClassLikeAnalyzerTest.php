<?php declare(strict_types=1);

namespace Rector\Tests\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use Rector\NodeAnalyzer\ClassLikeAnalyzer;
use Rector\Tests\AbstractContainerAwareTestCase;

final class ClassLikeAnalyzerTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ClassLikeAnalyzer
     */
    private $classLikeAnalyzer;

    protected function setUp(): void
    {
        $this->classLikeAnalyzer = $this->container->get(ClassLikeAnalyzer::class);
    }

    public function test(): void
    {
        $this->assertTrue($this->classLikeAnalyzer->isAnonymousClassNode(new Class_(null)));
        $this->assertFalse($this->classLikeAnalyzer->isAnonymousClassNode(new Class_('SomeClass')));
    }
}

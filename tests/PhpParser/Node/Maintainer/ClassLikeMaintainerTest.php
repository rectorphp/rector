<?php declare(strict_types=1);

namespace Rector\Tests\PhpParser\Node\Maintainer;

use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\Maintainer\ClassLikeMaintainer;
use Rector\Tests\AbstractContainerAwareTestCase;

final class ClassLikeMaintainerTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ClassLikeMaintainer
     */
    private $classLikeMaintainer;

    protected function setUp(): void
    {
        $this->classLikeMaintainer = $this->container->get(ClassLikeMaintainer::class);
    }

    public function test(): void
    {
        $this->assertTrue($this->classLikeMaintainer->isAnonymousClassNode(new Class_(null)));
        $this->assertFalse($this->classLikeMaintainer->isAnonymousClassNode(new Class_('SomeClass')));
    }
}

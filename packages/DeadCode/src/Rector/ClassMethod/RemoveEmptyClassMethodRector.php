<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\ClassMethodMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveEmptyClassMethodRector extends AbstractRector
{
    /**
     * @var ClassMethodMaintainer
     */
    private $classMethodMaintainer;

    public function __construct(ClassMethodMaintainer $classMethodMaintainer)
    {
        $this->classMethodMaintainer = $classMethodMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove empty method calls not required by parents', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class OrphanClass
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->getAttribute(Attribute::CLASS_NODE) instanceof Class_) {
            return null;
        }

        if ($node->stmts !== null && $node->stmts !== []) {
            return null;
        }

        if ($this->classMethodMaintainer->hasParentMethodOrInterfaceMethod($node)) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}

<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\ClassMethodMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveParentCallWithoutParentRector extends AbstractRector
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
        return new RectorDefinition('Remove unused parent call with no parent class', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
         parent::__construct();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
    }
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->class, 'parent')) {
            return null;
        }

        if ($node->getAttribute(Attribute::PARENT_CLASS_NAME) === null) {
            $this->removeNode($node);
            return null;
        }

        $methodNode = $node->getAttribute(Attribute::METHOD_NODE);
        if ($methodNode === null) {
            return null;
        }

        if ($this->classMethodMaintainer->hasParentMethodOrInterfaceMethod($methodNode)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}

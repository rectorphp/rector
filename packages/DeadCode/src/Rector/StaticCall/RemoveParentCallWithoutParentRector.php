<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\StaticCall\RemoveParentCallWithoutParentRector\RemoveParentCallWithoutParentRectorTest
 */
final class RemoveParentCallWithoutParentRector extends AbstractRector
{
    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    public function __construct(ClassMethodManipulator $classMethodManipulator)
    {
        $this->classMethodManipulator = $classMethodManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused parent call with no parent class', [
            new CodeSample(
                <<<'PHP'
class OrphanClass
{
    public function __construct()
    {
         parent::__construct();
    }
}
PHP
                ,
                <<<'PHP'
class OrphanClass
{
    public function __construct()
    {
    }
}
PHP
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
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        if (! $this->isName($node->class, 'parent')) {
            return null;
        }

        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            $this->removeNode($node);
            return null;
        }

        $methodNode = $node->getAttribute(AttributeKey::METHOD_NODE);
        if ($methodNode === null) {
            return null;
        }

        if ($this->isAnonymousClass($class)) {
            //currently the classMethodManipulator isn't able to find usages of anonymous classes
            return null;
        }

        $calledMethodName = $this->getName($node->name);
        if ($this->classMethodManipulator->hasParentMethodOrInterfaceMethod($methodNode, $calledMethodName)) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }
}

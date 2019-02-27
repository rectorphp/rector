<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveUnusedPrivateMethodRector extends AbstractRector
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
        return new RectorDefinition('Remove unused private method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run()
    {
        return 5;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->isPrivate()) {
            return null;
        }

        $classMethodCalls = $this->classMethodManipulator->getAllClassMethodCall($node);
        if ($classMethodCalls === []) {
            $this->removeNode($node);
        }

        return $node;
    }
}

<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\PhpParser\Node\Maintainer\ClassConstMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveUnusedPrivateConstantRector extends AbstractRector
{
    /**
     * @var ClassConstMaintainer
     */
    private $classConstMaintainer;

    public function __construct(ClassConstMaintainer $classConstMaintainer)
    {
        $this->classConstMaintainer = $classConstMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused private constant', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    private const SOME_CONSTANT = 5;
    public function run()
    {
        return 5;
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
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->isPrivate()) {
            return null;
        }

        if (count($node->consts) !== 1) {
            return null;
        }

        $classConstFetches = $this->classConstMaintainer->getAllClassConstFetch($node);

        // never used
        if ($classConstFetches === []) {
            $this->removeNode($node);
        }

        return $node;
    }
}

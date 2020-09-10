<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeCollector\NodeRepository;

/**
 * @see \Rector\SOLID\Tests\Rector\Class_\FinalizeClassesWithoutChildrenRector\FinalizeClassesWithoutChildrenRectorTest
 */
final class FinalizeClassesWithoutChildrenRector extends AbstractRector
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Finalize every class that has no children', [
            new CodeSample(
                <<<'PHP'
class FirstClass
{
}

class SecondClass
{
}

class ThirdClass extends SecondClass
{
}
PHP
                ,
                <<<'PHP'
final class FirstClass
{
}

class SecondClass
{
}

final class ThirdClass extends SecondClass
{
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFinal() || $node->isAbstract() || $this->isAnonymousClass($node)) {
            return null;
        }

        if ($this->isDoctrineEntityClass($node)) {
            return null;
        }

        if ($this->nodeRepository->hasClassChildren($node)) {
            return null;
        }

        $this->makeFinal($node);

        return $node;
    }
}

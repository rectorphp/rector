<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;

/**
 * @see \Rector\Restoration\Tests\Rector\Class_\RemoveFinalFromEntityRector\RemoveFinalFromEntityRectorTest
 */
final class RemoveFinalFromEntityRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove final from Doctrine entities', [
            new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class SomeClass
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isDoctrineEntityClass($node)) {
            return null;
        }

        if (! $node->isFinal()) {
            return null;
        }

        $this->removeFinal($node);

        return $node;
    }
}

<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Restoration\Tests\Rector\Class_\RemoveFinalFromEntityRector\RemoveFinalFromEntityRectorTest
 */
final class RemoveFinalFromEntityRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove final from Doctrine entities', [
            new CodeSample(
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class SomeClass
{
}
PHP
,
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
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

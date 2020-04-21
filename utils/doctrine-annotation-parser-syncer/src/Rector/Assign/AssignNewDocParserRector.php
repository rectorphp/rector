<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Assign;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;

final class AssignNewDocParserRector extends AbstractRector implements ClassSyncerRectorInterface
{
    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInClassNamed($node, AnnotationReader::class)) {
            return null;
        }

        if (! $this->isName($node->var, 'preParser')) {
            return null;
        }

        $node->expr = new New_(new FullyQualified(ConstantPreservingDocParser::class));

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change $this->preParser assign to new doc parser');
    }
}

<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\ClassMethod;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser;

final class ChangeOriginalTypeToCustomRector extends AbstractRector
{
    /**
     * @return class-string[]
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
        if (! $this->isInClassNamed($node, AnnotationReader::class)) {
            return null;
        }

        if (! $this->isName($node, '__construct')) {
            return null;
        }

        $firstParam = $node->params[0];
        $firstParam->type = new FullyQualified(ConstantPreservingDocParser::class);

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change DocParser type to custom one');
    }
}

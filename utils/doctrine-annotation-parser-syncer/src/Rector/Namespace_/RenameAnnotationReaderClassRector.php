<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;

final class RenameAnnotationReaderClassRector extends AbstractRector
{
    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Class_|null $firstClass */
        $firstClass = $this->betterNodeFinder->findFirstInstanceOf($node, Class_::class);
        if ($firstClass === null) {
            return null;
        }

        if (! $this->isName($firstClass, 'Doctrine\Common\Annotations\AnnotationReader')) {
            return null;
        }

        $firstClass->name = new Identifier('ConstantPreservingAnnotationReader');

        $node->name = new Name('Rector\DoctrineAnnotationGenerated');

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename AnnotationReader to own constant preserving format');
    }
}

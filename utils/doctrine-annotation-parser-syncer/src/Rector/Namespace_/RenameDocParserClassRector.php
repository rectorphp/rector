<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Rector\AbstractRector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RenameDocParserClassRector extends AbstractRector implements ClassSyncerRectorInterface
{
    /**
     * @return string[]
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

        if (! $this->isName($firstClass, 'Doctrine\Common\Annotations\DocParser')) {
            return null;
        }

        $firstClass->name = new Identifier('ConstantPreservingDocParser');
        $node->name = new Name('Rector\DoctrineAnnotationGenerated');

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename DocParser to own constant preserving format', [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace Doctrine\Common\Annotations;

class DocParser
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
namespace Rector\DoctrineAnnotationGenerated;

class ConstantPreservingDocParser
{
}
CODE_SAMPLE
            ),
        ]);
    }
}

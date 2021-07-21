<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\AliasedImportDouble\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddAliasImportRector extends AbstractRector
{
    public function __construct(
        private UseNodesToAddCollector $useNodesToAddCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('demo', []);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ClassMethod
    {
        $node->name = new Identifier('go');

        $this->useNodesToAddCollector->addUseImport(
            new AliasedObjectType('Extbase', 'TYPO3\CMS\Extbase\Annotation')
        );

        return $node;
    }
}

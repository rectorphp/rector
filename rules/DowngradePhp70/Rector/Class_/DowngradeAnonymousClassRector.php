<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp70\Rector\Class_\DowngradeAnonymousClassRector\DowngradeAnonymousClassRectorTest
 */
final class DowngradeAnonymousClassRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove anonymous class',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return new class {
            public function execute()
            {
            }
        };
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return new Anonymous();
    }
}
class Anonymous
{
    public function execute()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }

        $classNode = $this->betterNodeFinder->findParentType($node, Class_::class);
        if ($classNode instanceof Class_) {
            return $this->procesMoveAnonymousClass($node, $classNode);
        }

        return $node;
    }

    private function procesMoveAnonymousClass(Class_ $class, Class_ $classNode): void
    {
        $class->name = new Identifier('Anonymous');
        $this->addNodesAfterNode([$class], $classNode);

        $parent = $class->getAttribute(AttributeKey::PARENT_NODE);
    }
}

<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
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

    private function procesMoveAnonymousClass(Class_ $class, Class_ $classNode): Name
    {
        /** @var New_ $parent */
        $parent     = $class->getAttribute(AttributeKey::PARENT_NODE);
        //$argsString = '';
        //foreach ($parent->args as $arg) {
         //   $argsString .= ', ' . $this->betterStandardPrinter->print($arg);
       // }
        //$argsString = ltrim($argsString, ', ');

        $newClass = new Class_(
            new Name('Anonymous'),
            [
                'flags'      => $class->flags,
                'extends'    => $class->extends,
                'implements' => $class->implements,
                'stmts'      => $class->stmts,
                'attrGroups' => $class->attrGroups,
            ]
        );
        $this->addNodesAfterNode([$newClass], $classNode);

        return new Name('Anonymous');

        return new Name(sprintf('Anonymous(%s)', $argsString));
    }
}

<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
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
     * @var string
     */
    private const CLASS_NAME = 'Anonymous';

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
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
class Anonymous
{
    public function execute()
    {
    }
}
class SomeClass
{
    public function run()
    {
        return new Anonymous();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->classAnalyzer->isAnonymousClass($node->class)) {
            return null;
        }

        $classNode = $this->betterNodeFinder->findParentType($node, Class_::class);
        if ($classNode instanceof Class_) {
            return $this->procesMoveAnonymousClass($node, $classNode);
        }

        return $node;
    }

    private function getNamespacedClassName(?string $namespace, string $className): string
    {
        return $namespace === null
            ? $className
            : $namespace . '\\' . $className;;
    }

    private function procesMoveAnonymousClass(New_ $new, Class_ $class): New_
    {
        $namespace           = $class->getAttribute(AttributeKey::NAMESPACED_NAME);
        $className           = self::CLASS_NAME;
        $namespacedClassName = $this->getNamespacedClassName($namespace, $className);

        $count = 0;
        while (class_exists($namespacedClassName)) {
            $className           = $className . ++ $count;
            $namespacedClassName = $this->getNamespacedClassName($namespace, $className);
        }

        $newClass = new Class_(
            new Name($className),
            [
                'flags' => $new->class->flags,
                'extends' => $new->class->extends,
                'implements' => $new->class->implements,
                'stmts' => $new->class->stmts,
                'attrGroups' => $new->class->attrGroups,
            ]
        );
        $this->addNodeBeforeNode($newClass, $class);

        return new New_(new Name($className), $new->args);
    }
}

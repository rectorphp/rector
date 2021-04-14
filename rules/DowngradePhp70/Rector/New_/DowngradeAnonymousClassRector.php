<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
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
    private const CLASS_NAME = 'AnonymousFor_';

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
            return $this->procesMoveAnonymousClassInClass($node, $classNode);
        }

        $functionNode = $this->betterNodeFinder->findParentType($node, Function_::class);
        if ($functionNode instanceof Function_) {
            return $this->procesMoveAnonymousClassInFunction($node, $functionNode);
        }

        return $node;
    }

    private function getNamespacedClassName(string $namespace, string $className): string
    {
        return $namespace === ''
            ? $className
            : $namespace . '\\' . $className;;
    }

    private function getClassName(string $namespace, string $shortName): string
    {
        $className           = self::CLASS_NAME . $shortName;
        $namespacedClassName = $this->getNamespacedClassName($namespace, $className);

        $count = 0;
        while ($this->nodeRepository->findClass($namespacedClassName) || class_exists($namespacedClassName)) {
            $className           = $className . ++$count;
            $namespacedClassName = $this->getNamespacedClassName($namespace, $className);
        }

        return $className;
    }

    private function procesMoveAnonymousClassInClass(New_ $new, Class_ $class): New_
    {
        $namespacedClassName = $this->getName($class->namespacedName);
        $shortClassName      = $this->getName($class->name);
        $namespace           = $namespacedClassName === $shortClassName
            ? ''
            : substr($namespacedClassName, 0, - strlen($shortClassName) - 1);
        $className           = $this->getClassName($namespace, $shortClassName);

        return $this->processMove($new, $className, $class);
    }

    private function procesMoveAnonymousClassInFunction(New_ $new, Function_ $function): New_
    {
        $namespacedFunctionName = $this->getName($function);
        $shortFunctionName      = $this->getName($function->name);
        $namespace              = $namespacedFunctionName === $shortFunctionName
            ? null
            : substr($namespacedFunctionName, 0, - strlen($shortFunctionName) - 1);
        $className           = $this->getClassName($namespace, $shortFunctionName);

        return $this->processMove($new, $className, $function);
    }

    private function processMove(New_ $new, string $className, Node $node): New_
    {
        $class = new Class_(
            new Name($className),
            [
                'flags' => $new->class->flags,
                'extends' => $new->class->extends,
                'implements' => $new->class->implements,
                'stmts' => $new->class->stmts,
                'attrGroups' => $new->class->attrGroups,
            ]
        );
        $this->addNodeBeforeNode($class, $node);

        return new New_(new Name($className), $new->args);
    }
}

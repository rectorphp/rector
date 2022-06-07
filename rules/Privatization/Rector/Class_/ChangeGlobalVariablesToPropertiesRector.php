<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Global_;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/DWC4P
 *
 * @changelog https://stackoverflow.com/a/12446305/1348344
 * @see \Rector\Tests\Privatization\Rector\Class_\ChangeGlobalVariablesToPropertiesRector\ChangeGlobalVariablesToPropertiesRectorTest
 */
final class ChangeGlobalVariablesToPropertiesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $globalVariableNames = [];
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    public function __construct(PropertyToAddCollector $propertyToAddCollector)
    {
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change global $variables to private properties', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        global $variable;
        $variable = 5;
    }

    public function run()
    {
        global $variable;
        var_dump($variable);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $variable;
    public function go()
    {
        $this->variable = 5;
    }

    public function run()
    {
        var_dump($this->variable);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($node->getMethods() as $classMethod) {
            $this->collectGlobalVariableNamesAndRefactorToPropertyFetch($node, $classMethod);
        }
        if ($this->globalVariableNames === []) {
            return null;
        }
        foreach ($this->globalVariableNames as $globalVariableName) {
            $this->propertyToAddCollector->addPropertyWithoutConstructorToClass($globalVariableName, null, $node);
        }
        return $node;
    }
    private function collectGlobalVariableNamesAndRefactorToPropertyFetch(Class_ $class, ClassMethod $classMethod) : void
    {
        $this->globalVariableNames = [];
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use($class) {
            if ($node instanceof Global_) {
                $this->refactorGlobal($class, $node);
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            if ($node instanceof Variable) {
                return $this->refactorGlobalVariable($node);
            }
            return null;
        });
    }
    private function refactorGlobal(Class_ $class, Global_ $global) : void
    {
        foreach ($global->vars as $var) {
            $varName = $this->getName($var);
            if ($varName === null) {
                return;
            }
            if ($this->isReadOnly($class, $varName)) {
                return;
            }
            $this->globalVariableNames[] = $varName;
        }
        $this->removeNode($global);
    }
    private function refactorGlobalVariable(Variable $variable) : ?PropertyFetch
    {
        if (!$this->isNames($variable, $this->globalVariableNames)) {
            return null;
        }
        // replace with property fetch
        $variableName = $this->getName($variable);
        if ($variableName === null) {
            return null;
        }
        return $this->nodeFactory->createPropertyFetch('this', $variableName);
    }
    private function isReadOnly(Class_ $class, string $globalVariableName) : bool
    {
        /** @var ClassMethod[] $classMethods */
        $classMethods = $this->betterNodeFinder->findInstanceOf($class, ClassMethod::class);
        foreach ($classMethods as $classMethod) {
            $isReAssign = (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use($globalVariableName) : bool {
                if (!$node instanceof Assign) {
                    return \false;
                }
                if ($node->var instanceof Variable) {
                    return $this->nodeNameResolver->isName($node->var, $globalVariableName);
                }
                if ($node->var instanceof PropertyFetch) {
                    return $this->nodeNameResolver->isName($node->var, $globalVariableName);
                }
                return \false;
            });
            if ($isReAssign) {
                return \false;
            }
        }
        return \true;
    }
}

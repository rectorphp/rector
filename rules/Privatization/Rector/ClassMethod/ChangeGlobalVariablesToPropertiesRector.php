<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Global_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/DWC4P
 *
 * @changelog https://stackoverflow.com/a/12446305/1348344
 * @see \Rector\Tests\Privatization\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector\ChangeGlobalVariablesToPropertiesRectorTest
 */
final class ChangeGlobalVariablesToPropertiesRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private $globalVariableNames = [];
    /**
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    public function __construct(\Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector)
    {
        $this->propertyToAddCollector = $propertyToAddCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change global $variables to private properties', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $this->collectGlobalVariableNamesAndRefactorToPropertyFetch($classLike, $node);
        if ($this->globalVariableNames === []) {
            return null;
        }
        foreach ($this->globalVariableNames as $globalVariableName) {
            $this->propertyToAddCollector->addPropertyWithoutConstructorToClass($globalVariableName, null, $classLike);
        }
        return $node;
    }
    private function collectGlobalVariableNamesAndRefactorToPropertyFetch(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $this->globalVariableNames = [];
        $this->traverseNodesWithCallable($classMethod, function (\PhpParser\Node $node) use($class) : ?PropertyFetch {
            if ($node instanceof \PhpParser\Node\Stmt\Global_) {
                $this->refactorGlobal($class, $node);
                return null;
            }
            if ($node instanceof \PhpParser\Node\Expr\Variable) {
                return $this->refactorGlobalVariable($node);
            }
            return null;
        });
    }
    private function refactorGlobal(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\Global_ $global) : void
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
    private function refactorGlobalVariable(\PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Expr\PropertyFetch
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
    private function isReadOnly(\PhpParser\Node\Stmt\Class_ $class, string $globalVariableName) : bool
    {
        /** @var ClassMethod[] $classMethods */
        $classMethods = $this->betterNodeFinder->findInstanceOf($class, \PhpParser\Node\Stmt\ClassMethod::class);
        foreach ($classMethods as $classMethod) {
            $isReAssign = (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) use($globalVariableName) : bool {
                if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                    return \false;
                }
                if ($node->var instanceof \PhpParser\Node\Expr\Variable) {
                    return $this->nodeNameResolver->isName($node->var, $globalVariableName);
                }
                if ($node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
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

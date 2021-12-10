<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\ClassMethodAssignManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\Privatization\Naming\ConstantNaming;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\ChangeReadOnlyVariableWithDefaultValueToConstantRectorTest
 */
final class ChangeReadOnlyVariableWithDefaultValueToConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassMethodAssignManipulator
     */
    private $classMethodAssignManipulator;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator
     */
    private $varAnnotationManipulator;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\PropertyToAddCollector
     */
    private $propertyToAddCollector;
    /**
     * @readonly
     * @var \Rector\Privatization\Naming\ConstantNaming
     */
    private $constantNaming;
    public function __construct(\Rector\Core\NodeManipulator\ClassMethodAssignManipulator $classMethodAssignManipulator, \Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator $varAnnotationManipulator, \Rector\PostRector\Collector\PropertyToAddCollector $propertyToAddCollector, \Rector\Privatization\Naming\ConstantNaming $constantNaming)
    {
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
        $this->varAnnotationManipulator = $varAnnotationManipulator;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->constantNaming = $constantNaming;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change variable with read only status with default value to constant', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $replacements = [
            'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
            'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
        ];

        foreach ($replacements as $class => $method) {
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string[]
     */
    private const REPLACEMENTS = [
        'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
        'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
    ];

    public function run()
    {
        foreach (self::REPLACEMENTS as $class => $method) {
        }
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
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $readOnlyVariableAssigns = $this->collectReadOnlyVariableAssigns($node);
        $readOnlyVariableAssigns = $this->filterOutUniqueNames($readOnlyVariableAssigns);
        if ($readOnlyVariableAssigns === []) {
            return null;
        }
        foreach ($readOnlyVariableAssigns as $readOnlyVariableAssign) {
            $classMethod = $this->betterNodeFinder->findParentType($readOnlyVariableAssign, \PhpParser\Node\Stmt\ClassMethod::class);
            if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $methodName = $this->getName($classMethod);
            $classMethod = $node->getMethod($methodName);
            if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $this->refactorClassMethod($classMethod, $node, $readOnlyVariableAssigns);
        }
        return $node;
    }
    /**
     * @return Assign[]
     */
    private function collectReadOnlyVariableAssigns(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $readOnlyVariables = [];
        foreach ($class->getMethods() as $classMethod) {
            if ($this->isFoundByRefParam($classMethod)) {
                return [];
            }
            $readOnlyVariableAssignScalarVariables = $this->classMethodAssignManipulator->collectReadyOnlyAssignScalarVariables($classMethod);
            $readOnlyVariables = \array_merge($readOnlyVariables, $readOnlyVariableAssignScalarVariables);
        }
        return $readOnlyVariables;
    }
    /**
     * @param Assign[] $assigns
     * @return Assign[]
     */
    private function filterOutUniqueNames(array $assigns) : array
    {
        $assignsByName = $this->collectAssignsByName($assigns);
        $assignsWithUniqueName = [];
        foreach ($assignsByName as $assigns) {
            $count = \count($assigns);
            if ($count > 1) {
                continue;
            }
            $assignsWithUniqueName = \array_merge($assignsWithUniqueName, $assigns);
        }
        return $assignsWithUniqueName;
    }
    /**
     * @param Assign[] $assigns
     * @return array<string, Assign[]>
     */
    private function collectAssignsByName(array $assigns) : array
    {
        $assignsByName = [];
        foreach ($assigns as $assign) {
            /** @var string $variableName */
            $variableName = $this->getName($assign->var);
            $assignsByName[$variableName][] = $assign;
        }
        return $assignsByName;
    }
    /**
     * @param Assign[] $readOnlyVariableAssigns
     */
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Stmt\Class_ $class, array $readOnlyVariableAssigns) : void
    {
        foreach ($readOnlyVariableAssigns as $readOnlyVariableAssign) {
            /** @var Variable|ClassConstFetch $variable */
            $variable = $readOnlyVariableAssign->var;
            // already overridden
            if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            $variableName = $this->getName($variable);
            if ($variableName === null) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            foreach ($classMethod->getParams() as $param) {
                if ($this->nodeNameResolver->isName($param->var, $variableName)) {
                    continue 2;
                }
            }
            $this->removeNode($readOnlyVariableAssign);
            $classConst = $this->createPrivateClassConst($variable, $readOnlyVariableAssign->expr);
            // replace $variable usage in the code with constant
            $this->propertyToAddCollector->addConstantToClass($class, $classConst);
            $this->replaceVariableWithClassConstFetch($classMethod, $variableName, $classConst);
        }
    }
    private function isFoundByRefParam(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $params = $classMethod->getParams();
        foreach ($params as $param) {
            if ($param->byRef) {
                return \true;
            }
        }
        return \false;
    }
    private function createPrivateClassConst(\PhpParser\Node\Expr\Variable $variable, \PhpParser\Node\Expr $expr) : \PhpParser\Node\Stmt\ClassConst
    {
        $constantName = $this->constantNaming->createFromVariable($variable);
        if ($constantName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $const = new \PhpParser\Node\Const_($constantName, $expr);
        $classConst = new \PhpParser\Node\Stmt\ClassConst([$const]);
        $classConst->flags = \PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE;
        $this->mirrorComments($classConst, $variable);
        $constantType = $this->getType($classConst->consts[0]->value);
        $this->varAnnotationManipulator->decorateNodeWithType($classConst, $constantType);
        return $classConst;
    }
    private function replaceVariableWithClassConstFetch(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $variableName, \PhpParser\Node\Stmt\ClassConst $classConst) : void
    {
        $constantName = $this->getName($classConst);
        if ($constantName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $this->traverseNodesWithCallable($classMethod, function (\PhpParser\Node $node) use($variableName, $constantName) : ?ClassConstFetch {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $variableName)) {
                return null;
            }
            // replace with constant fetch
            return new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name('self'), new \PhpParser\Node\Identifier($constantName));
        });
    }
}

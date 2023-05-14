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
use PhpParser\Node\Stmt\Expression;
use Rector\BetterPhpDocParser\PhpDocManipulator\VarAnnotationManipulator;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\ClassMethodAssignManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Rector\Privatization\Naming\ConstantNaming;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\ChangeReadOnlyVariableWithDefaultValueToConstantRectorTest
 */
final class ChangeReadOnlyVariableWithDefaultValueToConstantRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $addedConstantNames = [];
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
    public function __construct(ClassMethodAssignManipulator $classMethodAssignManipulator, VarAnnotationManipulator $varAnnotationManipulator, PropertyToAddCollector $propertyToAddCollector, ConstantNaming $constantNaming)
    {
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
        $this->varAnnotationManipulator = $varAnnotationManipulator;
        $this->propertyToAddCollector = $propertyToAddCollector;
        $this->constantNaming = $constantNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change variable with read only status with default value to constant', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->addedConstantNames = [];
        foreach ($node->getMethods() as $classMethod) {
            $readOnlyVariableAssignScalarVariables = $this->classMethodAssignManipulator->collectReadyOnlyAssignScalarVariables($classMethod, $node);
            $this->refactorClassMethod($classMethod, $node, $readOnlyVariableAssignScalarVariables);
        }
        return $node;
    }
    /**
     * @param Assign[] $readOnlyVariableAssigns
     */
    private function refactorClassMethod(ClassMethod $classMethod, Class_ $class, array $readOnlyVariableAssigns) : void
    {
        foreach ($readOnlyVariableAssigns as $readOnlyVariableAssign) {
            $variable = $readOnlyVariableAssign->var;
            // already overridden
            if (!$variable instanceof Variable) {
                continue;
            }
            $variableName = $this->getName($variable);
            if ($variableName === null) {
                continue;
            }
            if ($this->isVariableParamName($classMethod, $variableName)) {
                continue;
            }
            $parentAssign = $readOnlyVariableAssign->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parentAssign instanceof Expression) {
                continue;
            }
            $constantName = $this->constantNaming->createFromVariable($variable);
            if ($constantName === null) {
                continue;
            }
            if (\in_array($constantName, $this->addedConstantNames, \true)) {
                continue;
            }
            $this->addedConstantNames[] = $constantName;
            $this->removeNode($readOnlyVariableAssign);
            $classConst = $this->createPrivateClassConst($variable, $readOnlyVariableAssign->expr);
            // replace $variable usage in the code with constant
            $this->propertyToAddCollector->addConstantToClass($class, $classConst);
            $this->replaceVariableWithClassConstFetch($classMethod, $variableName, $classConst);
        }
    }
    private function createPrivateClassConst(Variable $variable, Expr $expr) : ClassConst
    {
        $constantName = $this->constantNaming->createFromVariable($variable);
        if ($constantName === null) {
            throw new ShouldNotHappenException();
        }
        $const = new Const_($constantName, $expr);
        $classConst = new ClassConst([$const]);
        $classConst->flags = Class_::MODIFIER_PRIVATE;
        $this->mirrorComments($classConst, $variable);
        $constantType = $this->getType($classConst->consts[0]->value);
        $this->varAnnotationManipulator->decorateNodeWithType($classConst, $constantType);
        return $classConst;
    }
    private function replaceVariableWithClassConstFetch(ClassMethod $classMethod, string $variableName, ClassConst $classConst) : void
    {
        $constantName = $this->getName($classConst);
        if ($constantName === null) {
            throw new ShouldNotHappenException();
        }
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use($variableName, $constantName) : ?ClassConstFetch {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $variableName)) {
                return null;
            }
            // replace with constant fetch
            return new ClassConstFetch(new Name('self'), new Identifier($constantName));
        });
    }
    private function isVariableParamName(ClassMethod $classMethod, string $variableName) : bool
    {
        foreach ($classMethod->getParams() as $param) {
            if ($this->nodeNameResolver->isName($param->var, $variableName)) {
                return \true;
            }
        }
        return \false;
    }
}

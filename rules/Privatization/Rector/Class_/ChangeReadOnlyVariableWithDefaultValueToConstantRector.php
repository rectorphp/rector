<?php

declare(strict_types=1);

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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\PropertyToAddCollector;
use Stringy\Stringy;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\ChangeReadOnlyVariableWithDefaultValueToConstantRectorTest
 */
final class ChangeReadOnlyVariableWithDefaultValueToConstantRector extends AbstractRector
{
    public function __construct(
        private ClassMethodAssignManipulator $classMethodAssignManipulator,
        private VarAnnotationManipulator $varAnnotationManipulator,
        private PropertyToAddCollector $propertyToAddCollector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change variable with read only status with default value to constant',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $readOnlyVariableAssigns = $this->collectReadOnlyVariableAssigns($node);
        $readOnlyVariableAssigns = $this->filterOutUniqueNames($readOnlyVariableAssigns);

        if ($readOnlyVariableAssigns === []) {
            return null;
        }

        foreach ($readOnlyVariableAssigns as $readOnlyVariableAssign) {
            $classMethod = $readOnlyVariableAssign->getAttribute(AttributeKey::METHOD_NODE);
            if (! $classMethod instanceof ClassMethod) {
                throw new ShouldNotHappenException();
            }

            $methodName = $this->getName($classMethod);
            $classMethod = $node->getMethod($methodName);
            if (! $classMethod instanceof ClassMethod) {
                throw new ShouldNotHappenException();
            }

            $this->refactorClassMethod($classMethod, $node, $readOnlyVariableAssigns);
        }

        return $node;
    }

    /**
     * @return Assign[]
     */
    private function collectReadOnlyVariableAssigns(Class_ $class): array
    {
        $readOnlyVariables = [];

        foreach ($class->getMethods() as $classMethod) {
            if ($this->isFoundByRefParam($classMethod)) {
                return [];
            }

            $readOnlyVariableAssignScalarVariables = $this->classMethodAssignManipulator->collectReadyOnlyAssignScalarVariables(
                $classMethod
            );
            $readOnlyVariables = array_merge($readOnlyVariables, $readOnlyVariableAssignScalarVariables);
        }

        return $readOnlyVariables;
    }

    /**
     * @param Assign[] $assigns
     * @return Assign[]
     */
    private function filterOutUniqueNames(array $assigns): array
    {
        $assignsByName = $this->collectAssignsByName($assigns);
        $assignsWithUniqueName = [];
        foreach ($assignsByName as $assigns) {
            $count = count($assigns);
            if ($count > 1) {
                continue;
            }

            $assignsWithUniqueName = array_merge($assignsWithUniqueName, $assigns);
        }

        return $assignsWithUniqueName;
    }

    /**
     * @param Assign[] $assigns
     * @return array<string, Assign[]>
     */
    private function collectAssignsByName(array $assigns): array
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
    private function refactorClassMethod(ClassMethod $classMethod, Class_ $class, array $readOnlyVariableAssigns): void
    {
        foreach ($readOnlyVariableAssigns as $readOnlyVariableAssign) {
            $this->removeNode($readOnlyVariableAssign);

            /** @var Variable|ClassConstFetch $variable */
            $variable = $readOnlyVariableAssign->var;
            // already overridden
            if (! $variable instanceof Variable) {
                continue;
            }

            $classConst = $this->createPrivateClassConst($variable, $readOnlyVariableAssign->expr);

            // replace $variable usage in the code with constant
            $this->propertyToAddCollector->addConstantToClass($class, $classConst);

            $variableName = $this->getName($variable);
            if ($variableName === null) {
                throw new ShouldNotHappenException();
            }

            $this->replaceVariableWithClassConstFetch($classMethod, $variableName, $classConst);
        }
    }

    private function isFoundByRefParam(ClassMethod $classMethod): bool
    {
        $params = $classMethod->getParams();
        foreach ($params as $param) {
            if ($param->byRef) {
                return true;
            }
        }

        return false;
    }

    private function createPrivateClassConst(Variable $variable, Expr $expr): ClassConst
    {
        $constantName = $this->createConstantNameFromVariable($variable);

        $const = new Const_($constantName, $expr);

        $classConst = new ClassConst([$const]);
        $classConst->flags = Class_::MODIFIER_PRIVATE;

        $this->mirrorComments($classConst, $variable);

        $constantType = $this->getStaticType($classConst->consts[0]->value);
        $this->varAnnotationManipulator->decorateNodeWithType($classConst, $constantType);

        return $classConst;
    }

    private function replaceVariableWithClassConstFetch(
        ClassMethod $classMethod,
        string $variableName,
        ClassConst $classConst
    ): void {
        $constantName = $this->getName($classConst);
        if ($constantName === null) {
            throw new ShouldNotHappenException();
        }

        $this->traverseNodesWithCallable($classMethod, function (Node $node) use (
            $variableName,
            $constantName
        ): ?ClassConstFetch {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node, $variableName)) {
                return null;
            }

            // replace with constant fetch
            $classConstFetch = new ClassConstFetch(new Name('self'), new Identifier($constantName));

            // needed later
            $classConstFetch->setAttribute(AttributeKey::CLASS_NAME, $node->getAttribute(AttributeKey::CLASS_NAME));

            return $classConstFetch;
        });
    }

    private function createConstantNameFromVariable(Variable $variable): string
    {
        $variableName = $this->getName($variable);
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        $stringy = new Stringy($variableName);
        return (string) $stringy->underscored()
            ->toUpperCase();
    }
}

<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Matcher\VariableAndCallAssignMatcher;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\NamingConvention\NamingConventionAnalyzer;
use Rector\Naming\PhpDoc\VarTagValueNodeRenamer;
use Rector\Naming\ValueObject\VariableAndCallAssign;
use Rector\Naming\VariableRenamer;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

/**
 * @see \Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\RenameVariableToMatchMethodCallReturnTypeRectorTest
 */
final class RenameVariableToMatchMethodCallReturnTypeRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const ALLOWED_PARENT_TYPES = [ClassLike::class];

    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    /**
     * @var VariableRenamer
     */
    private $variableRenamer;

    /**
     * @var BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;

    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    /**
     * @var VariableAndCallAssignMatcher
     */
    private $variableAndCallAssignMatcher;

    /**
     * @var NamingConventionAnalyzer
     */
    private $namingConventionAnalyzer;

    /**
     * @var VarTagValueNodeRenamer
     */
    private $varTagValueNodeRenamer;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    public function __construct(
        BreakingVariableRenameGuard $breakingVariableRenameGuard,
        ExpectedNameResolver $expectedNameResolver,
        FamilyRelationsAnalyzer $familyRelationsAnalyzer,
        NamingConventionAnalyzer $namingConventionAnalyzer,
        TypeUnwrapper $typeUnwrapper,
        VarTagValueNodeRenamer $varTagValueNodeRenamer,
        VariableAndCallAssignMatcher $variableAndCallAssignMatcher,
        VariableRenamer $variableRenamer
    ) {
        $this->expectedNameResolver = $expectedNameResolver;
        $this->variableRenamer = $variableRenamer;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->variableAndCallAssignMatcher = $variableAndCallAssignMatcher;
        $this->namingConventionAnalyzer = $namingConventionAnalyzer;
        $this->varTagValueNodeRenamer = $varTagValueNodeRenamer;
        $this->typeUnwrapper = $typeUnwrapper;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename variable to match method return type', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $a = $this->getRunner();
    }

    public function getRunner(): Runner
    {
        return new Runner();
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $runner = $this->getRunner();
    }

    public function getRunner(): Runner
    {
        return new Runner();
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        $variableAndCallAssign = $this->variableAndCallAssignMatcher->match($node);
        if ($variableAndCallAssign === null) {
            return null;
        }

        $expectedName = $this->expectedNameResolver->resolveForCall($variableAndCallAssign->getCall());
        if ($expectedName === null || $this->isName($node->var, $expectedName)) {
            return null;
        }

        if ($this->shouldSkip($variableAndCallAssign, $expectedName)) {
            return null;
        }

        $this->renameVariable($variableAndCallAssign, $expectedName);

        return $node;
    }

    private function shouldSkip(VariableAndCallAssign $variableAndCallAssign, string $expectedName): bool
    {
        if ($this->namingConventionAnalyzer->isCallMatchingVariableName(
            $variableAndCallAssign->getCall(),
            $variableAndCallAssign->getVariableName(),
            $expectedName
        )) {
            return true;
        }

        if ($this->isClassTypeWithChildren($variableAndCallAssign->getCall())) {
            return true;
        }

        return $this->breakingVariableRenameGuard->shouldSkipVariable(
            $variableAndCallAssign->getVariableName(),
            $expectedName,
            $variableAndCallAssign->getFunctionLike(),
            $variableAndCallAssign->getVariable()
        );
    }

    private function renameVariable(VariableAndCallAssign $variableAndCallAssign, string $expectedName): void
    {
        $this->varTagValueNodeRenamer->renameAssignVarTagVariableName(
            $variableAndCallAssign->getAssign(),
            $variableAndCallAssign->getVariableName(),
            $expectedName
        );

        $this->variableRenamer->renameVariableInFunctionLike(
            $variableAndCallAssign->getFunctionLike(),
            $variableAndCallAssign->getAssign(),
            $variableAndCallAssign->getVariableName(),
            $expectedName
        );
    }

    /**
     * @param StaticCall|MethodCall|FuncCall $expr
     */
    private function isClassTypeWithChildren(Expr $expr): bool
    {
        $callStaticType = $this->getStaticType($expr);

        if ($callStaticType instanceof UnionType) {
            $callStaticType = $this->typeUnwrapper->unwrapNullableType($callStaticType);
        }

        if (! $callStaticType instanceof TypeWithClassName) {
            return false;
        }

        if (in_array($callStaticType->getClassName(), self::ALLOWED_PARENT_TYPES, true)) {
            return false;
        }

        return $this->familyRelationsAnalyzer->isParentClass($callStaticType->getClassName());
    }
}

<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Assign;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Matcher\VariableAndCallAssignMatcher;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\VariableRenamer;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\RenameVariableToMatchGetMethodNameRectorTest
 */
final class RenameVariableToMatchGetMethodNameRector extends AbstractRector
{
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

    public function __construct(
        ExpectedNameResolver $expectedNameResolver,
        VariableRenamer $variableRenamer,
        BreakingVariableRenameGuard $breakingVariableRenameGuard,
        FamilyRelationsAnalyzer $familyRelationsAnalyzer,
        VariableAndCallAssignMatcher $variableAndCallAssignMatcher
    ) {
        $this->expectedNameResolver = $expectedNameResolver;
        $this->variableRenamer = $variableRenamer;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->variableAndCallAssignMatcher = $variableAndCallAssignMatcher;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename variable to match get method name', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $a = $this->getRunner();
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

        $newName = $this->expectedNameResolver->resolveForGetCallExpr($variableAndCallAssign->getCall());
        if ($newName === null || $this->isName($node, $newName)) {
            return null;
        }

        if ($this->shouldSkipForNamingConvention(
            $variableAndCallAssign->getCall(),
            $variableAndCallAssign->getVariableName(),
            $newName
        )) {
            return null;
        }

        $callStaticType = $this->getStaticType($node->expr);
        if ($callStaticType instanceof TypeWithClassName && $this->familyRelationsAnalyzer->isParentClass(
            $callStaticType->getClassName()
        )) {
            return null;
        }

        if ($this->breakingVariableRenameGuard->shouldSkipVariable(
            $variableAndCallAssign->getVariableName(),
            $newName,
            $variableAndCallAssign->getFunctionLike(),
            $variableAndCallAssign->getVariable()
        )) {
            return null;
        }

        return $this->renameVariable($node, $newName, $variableAndCallAssign->getFunctionLike());
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    private function renameVariable(Assign $assign, string $newName, FunctionLike $functionLike): Assign
    {
        /** @var Variable $variableNode */
        $variableNode = $assign->var;

        /** @var string $originalName */
        $originalName = $variableNode->name;

        $this->renameInDocComment($assign, $originalName, $newName);

        $this->variableRenamer->renameVariableInFunctionLike($functionLike, $assign, $originalName, $newName);

        return $assign;
    }

    /**
     * @note variable rename is correct, but node printer doesn't see it as a changed text for some reason
     */
    private function renameInDocComment(Node $node, string $originalName, string $newName): void
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $varTagValueNode = $phpDocInfo->getByType(VarTagValueNode::class);
        if ($varTagValueNode === null) {
            return;
        }

        if ($varTagValueNode->variableName !== '$' . $originalName) {
            return;
        }

        $varTagValueNode->variableName = '$' . $newName;
    }

    /**
     * Keep cases like:
     *
     * $someNameSuffix = $this->getSomeName();
     * $prefixSomeName = $this->getSomeName();
     * $someName = $this->getSomeName();
     *
     * @param FuncCall|StaticCall|MethodCall $expr
     */
    private function shouldSkipForNamingConvention(Expr $expr, string $currentName, string $expectedName): bool
    {
        // skip "$call = $method->call();" based conventions
        $callName = $this->getName($expr->name);
        if ($currentName === $callName) {
            return true;
        }

        // starts with or ends with
        return (bool) Strings::match($currentName, '#^(' . $expectedName . '|' . $expectedName . '$)#i');
    }
}

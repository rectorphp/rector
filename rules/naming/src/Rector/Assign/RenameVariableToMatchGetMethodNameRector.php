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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
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

    public function __construct(
        ExpectedNameResolver $expectedNameResolver,
        VariableRenamer $variableRenamer,
        BreakingVariableRenameGuard $breakingVariableRenameGuard
    ) {
        $this->expectedNameResolver = $expectedNameResolver;
        $this->variableRenamer = $variableRenamer;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
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
        if (! $node->expr instanceof MethodCall && ! $node->expr instanceof StaticCall && ! $node->expr instanceof FuncCall) {
            return null;
        }

        if (! $node->var instanceof Variable) {
            return null;
        }

        $newName = $this->expectedNameResolver->resolveForGetCallExpr($node->expr);
        if ($newName === null || $this->isName($node, $newName)) {
            return null;
        }

        $currentName = $this->getName($node->var);
        if ($currentName === null) {
            return null;
        }

        // skip "$call = $method->call();" based conventions
        $callName = $this->getName($node->expr->name);
        if ($currentName === $callName) {
            return null;
        }

        $functionLike = $this->getCurrentFunctionLike($node);
        if ($functionLike === null) {
            return null;
        }

        if ($this->breakingVariableRenameGuard->shouldSkipVariable(
            $currentName,
            $newName,
            $functionLike,
            $node->var
        )) {
            return null;
        }

        return $this->renameVariable($node, $newName);
    }

    private function renameVariable(Assign $assign, string $newName): Assign
    {
        /** @var Variable $variableNode */
        $variableNode = $assign->var;

        /** @var string $originalName */
        $originalName = $variableNode->name;

        $this->renameInDocComment($assign, $originalName, $newName);

        $functionLike = $this->getCurrentFunctionLike($assign);
        if ($functionLike === null) {
            return $assign;
        }

        $this->variableRenamer->renameVariableInFunctionLike($functionLike, $assign, $originalName, $newName);

        return $assign;
    }

    /**
     * @return ClassMethod|Function_|Closure|null
     */
    private function getCurrentFunctionLike(Node $node): ?FunctionLike
    {
        return $node->getAttribute(AttributeKey::CLOSURE_NODE) ??
             $node->getAttribute(AttributeKey::METHOD_NODE) ??
             $node->getAttribute(AttributeKey::FUNCTION_NODE);
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

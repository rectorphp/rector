<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Assign;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\ExpectedNameResolver;

/**
 * @see \Rector\Naming\Tests\Rector\Assign\RenameVariableToMatchGetMethodNameRector\RenameVariableToMatchGetMethodNameRectorTest
 */
final class RenameVariableToMatchGetMethodNameRector extends AbstractRector
{
    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    public function __construct(ExpectedNameResolver $expectedNameResolver)
    {
        $this->expectedNameResolver = $expectedNameResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename variable to match get method name', [
            new CodeSample(
                <<<'PHP'
class SomeClass {
    public function run()
    {
        $a = $this->getRunner();
    }
}
PHP
,
                <<<'PHP'
class SomeClass {
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
        if (
            $node->expr instanceof ArrowFunction || ! $node->var instanceof Variable
        ) {
            return null;
        }

        $newName = $this->expectedNameResolver->resolveForGetCallExpr($node->expr);
        if ($newName === null || $this->isName($node, $newName)) {
            return null;
        }

        $skip = $this->skipOnConflict($node, $newName);
        if ($skip) {
            return null;
        }

        $this->renameVariable($node, $newName);

        return $node;
    }

    private function renameVariable(Node $node, string $newName): void
    {
        $parentNodeStmts = $this->getParentNodeStmts($node);

        /** @var Variable $variableNode */
        $variableNode = $node->var;

        /** @var string $originalName */
        $originalName = $variableNode->name;

        $this->traverseNodesWithCallable($parentNodeStmts, function (Node $node) use ($originalName, $newName) {
            /** @var Variable $node */
            if (! $this->isVariableName($node, $originalName)) {
                return null;
            }
            $this->renameInDocComment($node, $originalName, $newName);

            $node->name = $newName;
        });
    }

    /**
     * @return Stmt[]
     */
    private function getParentNodeStmts(Node $node): array
    {
        /** @var FunctionLike|null $parentNode */
        $parentNode = $this->findFirstFunctionLikeParent($node);

        if ($parentNode === null) {
            return [];
        }

        return $parentNode->getStmts() ?? [];
    }

    /**
     * @return Param[]
     */
    private function getParentNodeParams(Node $node): array
    {
        /** @var FunctionLike|null $parentNode */
        $parentNode = $this->findFirstFunctionLikeParent($node);

        if ($parentNode === null) {
            return [];
        }

        return $parentNode->getParams() ?? [];
    }

    /**
     * @return ClosureUse[]
     */
    private function getParentNodeUses(Node $node): array
    {
        /** @var FunctionLike|null $parentNode */
        $parentNode = $this->findFirstFunctionLikeParent($node);

        if ($parentNode === null || ! $parentNode instanceof Closure) {
            return [];
        }

        return $parentNode->uses ?? [];
    }

    private function skipOnConflict(Node $node, string $newName): bool
    {
        if ($this->skipOnConflictParamName($node, $newName)) {
            return true;
        }

        if ($this->skipOnConflictClosureUsesName($node, $newName)) {
            return true;
        }
        return $this->skipOnConflictOtherVariable($node, $newName);
    }

    private function findFirstFunctionLikeParent(Node $node): ?FunctionLike
    {
        return $this->betterNodeFinder->findFirstParentInstanceOf($node, FunctionLike::class);
    }

    private function skipOnConflictParamName(Node $node, string $newName): bool
    {
        $parentNodeParams = $this->getParentNodeParams($node);
        if ($parentNodeParams === []) {
            return false;
        }

        $originalName = $this->getName($node->var);

        $skip = false;
        $this->traverseNodesWithCallable($parentNodeParams, function (Node $node) use (
            $originalName,
            $newName,
            &$skip
        ): void {
            if (
                $node instanceof Param &&
                ($this->isVariableName($node->var, $newName) || $this->isVariableName($node->var, $originalName))
            ) {
                $skip = true;
            }
        });
        return $skip;
    }

    private function skipOnConflictClosureUsesName(Node $node, string $newName): bool
    {
        $skip = false;
        $parentNodeUses = $this->getParentNodeUses($node);
        $this->traverseNodesWithCallable($parentNodeUses, function (Node $node) use ($newName, &$skip): void {
            if ($this->isVariableName($node, $newName)) {
                $skip = true;
            }
        });
        return $skip;
    }

    private function skipOnConflictOtherVariable(Node $node, string $newName): bool
    {
        $skip = false;
        $parentNodeStmts = $this->getParentNodeStmts($node);
        $this->traverseNodesWithCallable($parentNodeStmts, function (Node $node) use ($newName, &$skip): void {
            /** @var Variable $node */
            if ($this->isVariableName($node, $newName)) {
                $skip = true;
            }
        });
        return $skip;
    }

    private function renameInDocComment(Variable $variable, string $originalName, string $newName): void
    {
        /** @var Doc $docComment */
        $docComment = $variable->getDocComment();
        if ($docComment !== null) {
            $newText = str_replace('$' . $originalName, '$' . $newName, $docComment->getText());
            $newDocComment = new Doc($newText);
            $variable->setDocComment($newDocComment);
        }
    }
}

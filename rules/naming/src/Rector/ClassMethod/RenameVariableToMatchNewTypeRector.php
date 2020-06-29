<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Naming\ExpectedNameResolver;

/**
 * @see \Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\RenameVariableToMatchNewTypeRectorTest
 */
final class RenameVariableToMatchNewTypeRector extends AbstractRector
{
    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    /**
     * @var BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;

    public function __construct(
        ExpectedNameResolver $expectedNameResolver,
        BreakingVariableRenameGuard $breakingVariableRenameGuard
    ) {
        $this->expectedNameResolver = $expectedNameResolver;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename variable to match new ClassType', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run()
    {
        $search = new DreamSearch();
        $search->advance();
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run()
    {
        $dreamSearch = new DreamSearch();
        $dreamSearch->advance();
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;

        $assignsOfNew = $this->getAssignsOfNew($node);
        foreach ($assignsOfNew as $assign) {
            $expectedName = $this->expectedNameResolver->resolveForAssignNew($assign);

            /** @var Variable $variable */
            $variable = $assign->var;

            if ($expectedName === null || $this->isName($variable, $expectedName)) {
                continue;
            }

            $currentName = $this->getName($variable);
            if ($currentName === null) {
                continue;
            }

            if ($this->breakingVariableRenameGuard->shouldSkip($currentName, $expectedName, $node, $variable)) {
                continue;
            }

            $hasChanged = true;

            // 1. rename assigned variable
            $assign->var = new Variable($expectedName);

            // 2. rename variable in the
            $this->renameVariableInClassMethod($node, $assign, $currentName, $expectedName);
        }

        if (! $hasChanged) {
            return null;
        }

        return $node;
    }

    /**
     * @return Assign[]
     */
    private function getAssignsOfNew(ClassMethod $classMethod): array
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Assign::class);

        return array_filter($assigns, function (Assign $assign) {
            return $assign->expr instanceof New_;
        });
    }

    private function renameVariableInClassMethod(
        ClassMethod $classMethod,
        Assign $assign,
        string $oldName,
        string $expectedName
    ): void {
        $isRenamingActive = false;

        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $oldName,
            $expectedName,
            $assign,
            &$isRenamingActive
        ) {
            if ($node === $assign) {
                $isRenamingActive = true;
                return null;
            }

            if (! $isRenamingActive) {
                return null;
            }

            if (! $this->isVariableName($node, $oldName)) {
                return null;
            }

            /** @var Variable $node */
            $node->name = new Identifier($expectedName);

            return $node;
        });
    }
}

<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Catch_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\CatchExceptionNameMatchingTypeRectorTest
 */
final class CatchExceptionNameMatchingTypeRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/xmfMAX/1
     */
    private const STARTS_WITH_ABBREVIATION_REGEX = '#^([A-Za-z]+?)([A-Z]{1}[a-z]{1})([A-Za-z]*)#';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Type and name of catch exception should match',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $typoException) {
            $typoException->getMessage();
        }
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $someException) {
            $someException->getMessage();
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
        return [Catch_::class];
    }

    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->types) !== 1) {
            return null;
        }

        if ($node->var === null) {
            return null;
        }

        $oldVariableName = $this->getName($node->var);
        if (! $oldVariableName) {
            return null;
        }

        $type = $node->types[0];
        $typeShortName = $this->nodeNameResolver->getShortName($type);

        $newVariableName = Strings::replace(
            lcfirst($typeShortName),
            self::STARTS_WITH_ABBREVIATION_REGEX,
            function (array $matches): string {
                $output = '';

                $output .= isset($matches[1]) ? strtolower($matches[1]) : '';
                $output .= $matches[2] ?? '';
                $output .= $matches[3] ?? '';

                return $output;
            }
        );

        if ($oldVariableName === $newVariableName) {
            return null;
        }

        $newVariable = new Variable($newVariableName);
        $isFoundInPrevious = (bool) $this->betterNodeFinder->findFirstPrevious(
            $node,
            fn (Node $n): bool => $this->nodeComparator->areNodesEqual($n, $newVariable)
        );

        if ($isFoundInPrevious) {
            return null;
        }

        $node->var->name = $newVariableName;
        $this->renameVariableInStmts($node, $oldVariableName, $newVariableName);

        return $node;
    }

    private function renameVariableInStmts(Catch_ $catch, string $oldVariableName, string $newVariableName): void
    {
        $this->traverseNodesWithCallable($catch->stmts, function (Node $node) use (
            $oldVariableName,
            $newVariableName
        ): void {
            if (! $node instanceof Variable) {
                return;
            }

            if (! $this->nodeNameResolver->isName($node, $oldVariableName)) {
                return;
            }

            $node->name = $newVariableName;
        });

        /** @var TryCatch $tryCatch */
        $tryCatch = $catch->getAttribute(AttributeKey::PARENT_NODE);
        $next = $tryCatch->getAttribute(AttributeKey::NEXT_NODE);

        $this->replaceNextUsageVariable($tryCatch, $next, $oldVariableName, $newVariableName);
    }

    private function replaceNextUsageVariable(
        Node $currentNode,
        ?Node $nextNode,
        string $oldVariableName,
        string $newVariableName
    ): void {
        if (! $nextNode instanceof Node) {
            $parent = $currentNode->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parent instanceof Node) {
                return;
            }
            if ($parent instanceof FunctionLike) {
                return;
            }
            $nextNode = $parent->getAttribute(AttributeKey::NEXT_NODE);
            $this->replaceNextUsageVariable($parent, $nextNode, $oldVariableName, $newVariableName);

            return;
        }

        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->find($nextNode, function (Node $node) use ($oldVariableName): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->nodeNameResolver->isName($node, $oldVariableName);
        });

        $processRenameVariables = $this->processRenameVariable($variables, $oldVariableName, $newVariableName);
        if (! $processRenameVariables) {
            return;
        }

        $currentNode = $nextNode;
        $nextNode = $nextNode->getAttribute(AttributeKey::NEXT_NODE);
        $this->replaceNextUsageVariable($currentNode, $nextNode, $oldVariableName, $newVariableName);
    }

    /**
     * @param Variable[] $variables
     */
    private function processRenameVariable(array $variables, string $oldVariableName, string $newVariableName): bool
    {
        foreach ($variables as $variable) {
            $parent = $variable->getAttribute(AttributeKey::PARENT_NODE);
            if ($parent instanceof Assign && $this->nodeComparator->areNodesEqual(
                $parent->var,
                $variable
            ) && $this->nodeNameResolver->isName($parent->var, $oldVariableName)
            && ! $this->nodeComparator->areNodesEqual($parent->expr, $variable)
            ) {
                return false;
            }
            $variable->name = $newVariableName;
        }

        return true;
    }
}

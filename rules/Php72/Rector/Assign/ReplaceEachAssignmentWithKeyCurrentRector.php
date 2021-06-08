<?php

declare(strict_types=1);

namespace Rector\Php72\Rector\Assign;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\ReplaceEachAssignmentWithKeyCurrentRectorTest
 */
final class ReplaceEachAssignmentWithKeyCurrentRector extends AbstractRector
{
    /**
     * @var string
     */
    private const KEY = 'key';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace each() assign outside loop', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$array = ['b' => 1, 'a' => 2];
$eachedArray = each($array);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$array = ['b' => 1, 'a' => 2];
$eachedArray[1] = current($array);
$eachedArray['value'] = current($array);
$eachedArray[0] = key($array);
$eachedArray['key'] = key($array);
next($array);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var FuncCall $eachFuncCall */
        $eachFuncCall = $node->expr;
        $eachedVariable = $eachFuncCall->args[0]->value;

        $assignVariable = $node->var;

        $newNodes = $this->createNewNodes($assignVariable, $eachedVariable);
        $this->addNodesAfterNode($newNodes, $node);
        $this->removeNode($node);

        return null;
    }

    private function shouldSkip(Assign $assign): bool
    {
        if (! $assign->expr instanceof FuncCall) {
            return true;
        }

        if (! $this->nodeNameResolver->isName($assign->expr, 'each')) {
            return true;
        }

        $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof While_) {
            return true;
        }
        // skip assign to List
        if (! $parentNode instanceof Assign) {
            return false;
        }
        return $parentNode->var instanceof List_;
    }

    /**
     * @return array<int, Assign|FuncCall>
     */
    private function createNewNodes(Expr $assignVariable, Expr $eachedVariable): array
    {
        $newNodes = [];

        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 1, 'current');
        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 'value', 'current');

        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 0, self::KEY);
        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, self::KEY, self::KEY);

        $newNodes[] = $this->nodeFactory->createFuncCall('next', [new Arg($eachedVariable)]);

        return $newNodes;
    }

    private function createDimFetchAssignWithFuncCall(
        Expr $assignVariable,
        Expr $eachedVariable,
        string | int $dimValue,
        string $functionName
    ): Assign {
        $dim = BuilderHelpers::normalizeValue($dimValue);
        $arrayDimFetch = new ArrayDimFetch($assignVariable, $dim);

        return new Assign($arrayDimFetch, $this->nodeFactory->createFuncCall(
            $functionName,
            [new Arg($eachedVariable)]
        ));
    }
}

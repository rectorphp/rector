<?php declare(strict_types=1);

namespace Rector\Php\Rector\Each;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @source https://wiki.php.net/rfc/deprecations_php_7_2#each
 */
final class ListEachRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'each() function is deprecated, use foreach() instead.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
list($key, $callback) = each($callbacks);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$key = key($opt->option);
$val = current($opt->option);
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        if ($this->shouldSkip($assignNode)) {
            return $assignNode;
        }

        /** @var List_ $listNode */
        $listNode = $assignNode->var;

        /** @var FuncCall $eachFuncCall */
        $eachFuncCall = $assignNode->expr;

        // only key: list($key, ) = each($values);
        if ($listNode->items[0] && $listNode->items[1] === null) {
            $keyFuncCall = $this->createFuncCallWithNameAndArgs('key', $eachFuncCall->args);
            return new Assign($listNode->items[0]->value, $keyFuncCall);
        }

        // only value: list(, $value) = each($values);
        if ($listNode->items[1] && $listNode->items[0] === null) {
            $nextFuncCall = $this->createFuncCallWithNameAndArgs('next', $eachFuncCall->args);
            $this->addNodeAfterNode($nextFuncCall, $assignNode);

            $currentFuncCall = $this->createFuncCallWithNameAndArgs('current', $eachFuncCall->args);
            return new Assign($listNode->items[1]->value, $currentFuncCall);
        }

        // both: list($key, $value) = each($values);
        // ↓
        // $key = key($values);
        // $value = current($values);
        // next($values); - only inside a loop
        $currentFuncCall = $this->createFuncCallWithNameAndArgs('current', $eachFuncCall->args);
        $assignCurrentNode = new Assign($listNode->items[1]->value, $currentFuncCall);
        $this->addNodeAfterNode($assignCurrentNode, $assignNode);

        if ($this->isInsideDoWhile($assignNode)) {
            $nextFuncCall = $this->createFuncCallWithNameAndArgs('next', $eachFuncCall->args);
            $this->addNodeAfterNode($nextFuncCall, $assignNode);
        }

        $keyFuncCall = $this->createFuncCallWithNameAndArgs('key', $eachFuncCall->args);
        return new Assign($listNode->items[0]->value, $keyFuncCall);
    }

    private function isListToEachAssign(Assign $assignNode): bool
    {
        if (! $assignNode->var instanceof List_) {
            return false;
        }

        return $assignNode->expr instanceof FuncCall && (string) $assignNode->expr->name === 'each';
    }

    /**
     * @param Arg[] $args
     */
    private function createFuncCallWithNameAndArgs(string $name, array $args): FuncCall
    {
        return new FuncCall(new Name($name), $args);
    }

    /**
     * Is inside the "do {} while ();" loop → need to add "next()"
     */
    private function isInsideDoWhile(Node $assignNode): bool
    {
        $parentNode = $assignNode->getAttribute(Attribute::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            return false;
        }

        $parentParentNode = $parentNode->getAttribute(Attribute::PARENT_NODE);

        return $parentParentNode instanceof Do_;
    }

    private function shouldSkip(Assign $assignNode): bool
    {
        if (! $this->isListToEachAssign($assignNode)) {
            return true;
        }

        // assign should be top level, e.g. not in a while loop
        if (! $assignNode->getAttribute(Attribute::PARENT_NODE) instanceof Expression) {
            return true;
        }

        /** @var List_ $listNode */
        $listNode = $assignNode->var;

        if (count($listNode->items) !== 2) {
            return true;
        }

        // empty list → cannot handle
        return $listNode->items[0] === null && $listNode->items[1] === null;
    }
}

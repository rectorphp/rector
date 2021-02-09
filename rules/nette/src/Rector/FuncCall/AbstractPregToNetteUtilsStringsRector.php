<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\Contract\PregToNetteUtilsStringInterface;

/**
 * @see https://www.tomasvotruba.cz/blog/2019/02/07/what-i-learned-by-using-thecodingmachine-safe/#is-there-a-better-way
 *
 * @see \Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\PregMatchFunctionToNetteUtilsStringsRectorTest
 */
abstract class AbstractPregToNetteUtilsStringsRector extends AbstractRector implements PregToNetteUtilsStringInterface
{
    /**
     * @param FuncCall|Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identical) {
            return $this->refactorIdentical($node);
        }

        return $this->refactorFuncCall($node);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, Identical::class];
    }

    /**
     * @param array<string, string> $functionRenameMap
     */
    protected function matchFuncCallRenameToMethod(FuncCall $funcCall, array $functionRenameMap): ?string
    {
        $oldFunctionNames = array_keys($functionRenameMap);
        if (! $this->isNames($funcCall, $oldFunctionNames)) {
            return null;
        }

        $currentFunctionName = $this->getName($funcCall);
        return $functionRenameMap[$currentFunctionName];
    }

    /**
     * @param Expr $expr
     */
    protected function createBoolCast(?Node $node, Node $expr): Bool_
    {
        if ($node instanceof Return_ && $expr instanceof Assign) {
            $expr = $expr->expr;
        }

        return new Bool_($expr);
    }
}

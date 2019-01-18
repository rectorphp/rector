<?php declare(strict_types=1);

namespace Rector\PhpParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;

final class NodeTransformer
{
    /**
     * From:
     * - sprintf("Hi %s", $name);
     *
     * to:
     * - ["Hi %s", $name]
     */
    public function transformSprintfToArray(FuncCall $sprintfFuncCall): ?Array_
    {
        /** @var Node[] $arrayItems */
        [$arrayItems, $stringArgument] = $this->splitMessageAndArgs($sprintfFuncCall);
        if (! $stringArgument instanceof String_) {
            // we need to know "%x" parts → nothing we can do
            return null;
        }

        if ($arrayItems === []) {
            return null;
        }

        $message = $stringArgument->value;
        $messageParts = $this->splitBySpace($message);

        foreach ($messageParts as $key => $messagePart) {
            // is mask
            if (Strings::match($messagePart, '#^%\w$#')) {
                /** @var Node[] $arrayItems */
                $messageParts[$key] = array_shift($arrayItems);
            } else {
                $messageParts[$key] = new String_($messagePart);
            }
        }

        return new Array_($messageParts);
    }

    /**
     * @param Yield_[]|Expression[] $yieldNodes
     */
    public function transformYieldsToArray(array $yieldNodes): Array_
    {
        $arrayItems = [];
        foreach ($yieldNodes as $yieldNode) {
            if ($yieldNode instanceof Expression) {
                $yieldNode = $yieldNode->expr;
            }

            if (! $yieldNode instanceof Yield_) {
                continue;
            }

            if ($yieldNode->value === null) {
                continue;
            }

            $arrayItems[] = new ArrayItem($yieldNode->value, $yieldNode->key);
        }

        return new Array_($arrayItems);
    }

    /**
     * @return Expression[]
     */
    public function transformArrayToYields(Array_ $arrayNode): array
    {
        $yieldNodes = [];

        foreach ($arrayNode->items as $arrayItem) {
            $expressionNode = new Expression(new Yield_($arrayItem->value, $arrayItem->key));
            if ($arrayItem->getComments()) {
                $expressionNode->setAttribute('comments', $arrayItem->getComments());
            }

            $yieldNodes[] = $expressionNode;
        }

        return $yieldNodes;
    }

    public function transformConcatToStringArray(Concat $concatNode): ?Array_
    {
        $arrayItems = $this->transformConcatToItems($concatNode);

        return new Array_($arrayItems);
    }

    /**
     * @return Node[][]|null[][]|Node[]|null[]
     */
    private function splitMessageAndArgs(FuncCall $sprintfFuncCall): array
    {
        $stringArgument = null;
        $arrayItems = [];
        foreach ($sprintfFuncCall->args as $i => $arg) {
            if ($i === 0) {
                $stringArgument = $arg->value;
            } else {
                $arrayItems[] = $arg->value;
            }
        }

        return [$arrayItems, $stringArgument];
    }

    /**
     * @return string[]
     */
    private function splitBySpace(string $value): array
    {
        $value = str_getcsv($value, ' ');

        return array_filter($value);
    }

    /**
     * @return mixed[]
     */
    private function transformConcatToItems(Concat $concatNode): array
    {
        $arrayItems = $this->transformConcatItemToArrayItems($concatNode->left);

        return array_merge($arrayItems, $this->transformConcatItemToArrayItems($concatNode->right));
    }

    /**
     * @return Node[]|string[]
     */
    private function transformConcatItemToArrayItems(Expr $node): array
    {
        if ($node instanceof Concat) {
            return $this->transformConcatToItems($node);
        }

        if (! $node instanceof String_) {
            return [$node];
        }

        $arrayItems = [];
        $parts = $this->splitBySpace($node->value);
        foreach ($parts as $part) {
            if (trim($part)) {
                $arrayItems[] = new String_($part);
            }
        }

        return $arrayItems;
    }
}

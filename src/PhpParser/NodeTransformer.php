<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\SprintfStringAndArgs;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @api used in phpunit
 */
final class NodeTransformer
{
    /**
     * @var string
     * @see https://regex101.com/r/XFc3qA/1
     */
    private const PERCENT_TEXT_REGEX = '#^%\\w$#';
    /**
     * @api used in phpunit symfony
     *
     * From:
     * - sprintf("Hi %s", $name);
     *
     * to:
     * - ["Hi %s", $name]
     */
    public function transformSprintfToArray(FuncCall $sprintfFuncCall) : ?Array_
    {
        $sprintfStringAndArgs = $this->splitMessageAndArgs($sprintfFuncCall);
        if (!$sprintfStringAndArgs instanceof SprintfStringAndArgs) {
            return null;
        }
        $arrayItems = $sprintfStringAndArgs->getArrayItems();
        $stringValue = $sprintfStringAndArgs->getStringValue();
        $messageParts = $this->splitBySpace($stringValue);
        $arrayMessageParts = [];
        foreach ($messageParts as $messagePart) {
            if (StringUtils::isMatch($messagePart, self::PERCENT_TEXT_REGEX)) {
                /** @var Expr $messagePartNode */
                $messagePartNode = \array_shift($arrayItems);
            } else {
                $messagePartNode = new String_($messagePart);
            }
            $arrayMessageParts[] = new ArrayItem($messagePartNode);
        }
        return new Array_($arrayMessageParts);
    }
    /**
     * @return Expression[]
     */
    public function transformArrayToYields(Array_ $array) : array
    {
        $yields = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            $yield = new Yield_($arrayItem->value, $arrayItem->key);
            $expression = new Expression($yield);
            $arrayItemComments = $arrayItem->getComments();
            if ($arrayItemComments !== []) {
                $expression->setAttribute(AttributeKey::COMMENTS, $arrayItemComments);
            }
            $yields[] = $expression;
        }
        return $yields;
    }
    /**
     * @api symfony
     */
    public function transformConcatToStringArray(Concat $concat) : Array_
    {
        $arrayItems = $this->transformConcatToItems($concat);
        $expr = BuilderHelpers::normalizeValue($arrayItems);
        if (!$expr instanceof Array_) {
            throw new ShouldNotHappenException();
        }
        return $expr;
    }
    private function splitMessageAndArgs(FuncCall $sprintfFuncCall) : ?SprintfStringAndArgs
    {
        $stringArgument = null;
        $arrayItems = [];
        foreach ($sprintfFuncCall->args as $i => $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if ($i === 0) {
                $stringArgument = $arg->value;
            } else {
                $arrayItems[] = $arg->value;
            }
        }
        if (!$stringArgument instanceof String_) {
            return null;
        }
        if ($arrayItems === []) {
            return null;
        }
        return new SprintfStringAndArgs($stringArgument, $arrayItems);
    }
    /**
     * @return string[]
     */
    private function splitBySpace(string $value) : array
    {
        $value = \str_getcsv($value, ' ');
        return \array_filter($value);
    }
    /**
     * @return mixed[]
     */
    private function transformConcatToItems(Concat $concat) : array
    {
        $arrayItems = $this->transformConcatItemToArrayItems($concat->left);
        return \array_merge($arrayItems, $this->transformConcatItemToArrayItems($concat->right));
    }
    /**
     * @return mixed[]|Expr[]|String_[]
     */
    private function transformConcatItemToArrayItems(Expr $expr) : array
    {
        if ($expr instanceof Concat) {
            return $this->transformConcatToItems($expr);
        }
        if (!$expr instanceof String_) {
            return [$expr];
        }
        $arrayItems = [];
        $parts = $this->splitBySpace($expr->value);
        foreach ($parts as $part) {
            if (\trim($part) !== '') {
                $arrayItems[] = new String_($part);
            }
        }
        return $arrayItems;
    }
}

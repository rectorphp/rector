<?php declare(strict_types=1);

namespace Rector\Rector\CodeQuality;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class InArrayAndArrayKeysToArrayKeyExistsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify `in_array` and `array_keys` functions combination into `array_key_exists` when `array_keys` has one argument only',
            [new CodeSample('in_array("key", array_keys($array), true);', 'array_key_exists("key", $array);')]
        );
    }

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    /**
     * @param FuncCall $funcCall
     */
    public function refactor(Node $funcCall): ?Node
    {
        if (! $this->isInArrayFunction($funcCall)) {
            return null;
        }
        /** @var FuncCall $inArrayFunction */
        $inArrayFunction = $funcCall;
        $secondArgument = $inArrayFunction->args[1]->value;
        if (! $secondArgument instanceof FuncCall) {
            return null;
        }
        /** @var Name $functionName */
        $functionName = $secondArgument->name;
        if ($functionName->toString() !== 'array_keys') {
            return null;
        }
        if (count($secondArgument->args) > 1) {
            return null;
        }

        [$key, $array] = $funcCall->args;

        $array = $array->value->args[0];

        $funcCall->args = [$key, $array];

        $funcCall->name = new Name('array_key_exists');

        return $funcCall;
    }

    private function isInArrayFunction(Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        /** @var Name $funcCallName */
        $funcCallName = $node->name;

        if (! $funcCallName instanceof Name) {
            return false;
        }

        return $funcCallName->toString() === 'in_array';
    }
}

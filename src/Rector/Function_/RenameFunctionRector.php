<?php declare(strict_types=1);

namespace Rector\Rector\Function_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RenameFunctionRector extends AbstractRector
{
    /**
     * @var string[]|string[][]
     */
    private $oldFunctionToNewFunction = [];

    /**
     * @param string[]|string[][] $oldFunctionToNewFunction
     */
    public function __construct(array $oldFunctionToNewFunction)
    {
        $this->oldFunctionToNewFunction = $oldFunctionToNewFunction;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function call new one.', [
            new ConfiguredCodeSample(
                'view("...", []);',
                'Laravel\Templating\render("...", []);',
                [
                    'view' => 'Laravel\Templating\render',
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldFunctionToNewFunction as $oldFunction => $newFunction) {
            if (! $this->isNameInsensitive($node, $oldFunction)) {
                continue;
            }

            // rename of function into wrap function
            // e.g. one($arg) → three(two($agr));
            if (is_array($newFunction)) {
                return $this->wrapFuncCalls($node, $newFunction);
            }

            $node->name = Strings::contains($newFunction, '\\') ? new FullyQualified($newFunction) : new Name(
                $newFunction
            );
        }

        return $node;
    }

    /**
     * @param string[] $newFunctions
     */
    private function wrapFuncCalls(FuncCall $funcCall, array $newFunctions): FuncCall
    {
        $previousNode = null;
        $newFunctions = array_reverse($newFunctions);

        foreach ($newFunctions as $wrapFunction) {
            $arguments = $previousNode === null ? $funcCall->args : [new Arg($previousNode)];

            $funcCall = new FuncCall(new FullyQualified($wrapFunction), $arguments);
            $previousNode = $funcCall;
        }

        return $funcCall;
    }
}

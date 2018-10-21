<?php declare(strict_types=1);

namespace Rector\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class FunctionReplaceRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldFunctionToNewFunction = [];

    /**
     * @param string[] $oldFunctionToNewFunction
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
                    '$functionToStaticCall' => [
                        'view' => 'Laravel\Templating\render',
                    ],
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
        // anonymous function
        if (! $node->name instanceof Name) {
            return null;
        }

        $functionName = $node->name->toString();
        if (! isset($this->oldFunctionToNewFunction[$functionName])) {
            return null;
        }

        $newFunctionName = $this->oldFunctionToNewFunction[$functionName];

        $functCallNode = new FuncCall(new FullyQualified($newFunctionName));
        $functCallNode->args = $node->args;

        return $functCallNode;
    }
}

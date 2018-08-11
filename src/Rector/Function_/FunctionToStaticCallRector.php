<?php declare(strict_types=1);

namespace Rector\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class FunctionToStaticCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $functionToStaticCall = [];

    /**
     * @param string[] $functionToMethodCall
     */
    public function __construct(array $functionToMethodCall)
    {
        $this->functionToStaticCall = $functionToMethodCall;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function call to static method call.', [
            new ConfiguredCodeSample(
                'view("...", []);',
                'SomeClass::render("...", []);',
                [
                    '$functionToStaticCall' => [
                        'view' => ['SomeStaticClass', 'render'],
                    ],
                ]
            ),
        ]);
    }

    /**
     * future compatibility
     */
    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function isCandidate(Node $node): bool
    {
        return true;
    }

    public function refactor(Node $node): ?Node
    {
        if (! $node instanceof FuncCall) {
            return $node;
        }

        // anonymous function
        if (! $node->name instanceof Name) {
            return $node;
        }

        $functionName = $node->name->toString();
        if (! isset($this->functionToStaticCall[$functionName])) {
            return $node;
        }

        [$className, $methodName] = $this->functionToStaticCall[$functionName];

        $staticCallNode = new StaticCall(new FullyQualified($className), $methodName);
        $staticCallNode->args = $node->args;

        return $staticCallNode;
    }
}

<?php declare(strict_types=1);

namespace Rector\Rector\Function_;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
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
     * @param string[] $functionToStaticCall
     */
    public function __construct(array $functionToStaticCall)
    {
        $this->functionToStaticCall = $functionToStaticCall;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function call to static method call.', [
            new ConfiguredCodeSample(
                'view("...", []);',
                'SomeClass::render("...", []);',
                [
                    'view' => ['SomeStaticClass', 'render'],
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
        foreach ($this->functionToStaticCall as $function => $staticCall) {
            if (! $this->isName($node, $function)) {
                continue;
            }

            [$className, $methodName] = $staticCall;

            return new StaticCall(new FullyQualified($className), $methodName, $node->args);
        }

        return null;
    }
}

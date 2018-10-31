<?php declare(strict_types=1);

namespace Rector\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class StaticCallToFunctionRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $staticCallToFunctionByType = [];

    /**
     * @param string[][] $staticCallToFunctionByType
     */
    public function __construct(array $staticCallToFunctionByType)
    {
        $this->staticCallToFunctionByType = $staticCallToFunctionByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns static call to function call.', [
            new ConfiguredCodeSample(
                'OldClass::oldMethod("args");',
                'new_function("args");',
                [
                    '$staticCallToFunction' => [
                        'OldClass' => [
                            'oldMethod' => 'new_function',
                        ],
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->staticCallToFunctionByType as $type => $methodNamesToFunctions) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($methodNamesToFunctions as $methodName => $function) {
                if (! $this->isName($node, $methodName)) {
                    continue;
                }

                return new FuncCall(new FullyQualified($function), $node->args);
            }
        }

        return null;
    }
}

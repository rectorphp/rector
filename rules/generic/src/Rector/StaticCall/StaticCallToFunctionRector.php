<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\StaticCall\StaticCallToFunctionRector\StaticCallToFunctionRectorTest
 */
final class StaticCallToFunctionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const STATIC_CALL_TO_FUNCTION_BY_TYPE = 'static_call_to_function_by_type';

    /**
     * @var string[][]
     */
    private $staticCallToFunctionByType = [];

    /**
     * @param string[][] $staticCallToFunctionByType
     */
    public function __construct(array $staticCallToFunctionByType = [])
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
                    self::STATIC_CALL_TO_FUNCTION_BY_TYPE => [
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
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            foreach ($methodNamesToFunctions as $methodName => $function) {
                if (! $this->isName($node->name, $methodName)) {
                    continue;
                }

                return new FuncCall(new FullyQualified($function), $node->args);
            }
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->staticCallToFunctionByType = $configuration[self::STATIC_CALL_TO_FUNCTION_BY_TYPE] ?? [];
    }
}

<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Transform\ValueObject\FuncNameToStaticCallName;

/**
 * @see \Rector\Generic\Tests\Rector\FuncCall\FuncCallToStaticCallRector\FuncCallToStaticCallRectorTest
 */
final class FuncCallToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNC_CALLS_TO_STATIC_CALLS = 'func_calls_to_static_calls';

    /**
     * @var FuncNameToStaticCallName[]
     */
    private $funcNameToStaticCallNames = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined function call to static method call.', [
            new ConfiguredCodeSample(
                'view("...", []);',
                'SomeClass::render("...", []);',
                [
                    self::FUNC_CALLS_TO_STATIC_CALLS => [
                        new FuncNameToStaticCallName('view', 'SomeStaticClass', 'render'),
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
        foreach ($this->funcNameToStaticCallNames as $funcNameToStaticCallName) {
            if (! $this->isName($node, $funcNameToStaticCallName->getOldFuncName())) {
                continue;
            }

            return $this->createStaticCall(
                $funcNameToStaticCallName->getNewClassName(),
                $funcNameToStaticCallName->getNewMethodName(),
                $node->args
            );
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->funcNameToStaticCallNames = $configuration[self::FUNC_CALLS_TO_STATIC_CALLS] ?? [];
    }
}

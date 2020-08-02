<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Renaming\Tests\Rector\FuncCall\RenameFuncCallToStaticCallRector\RenameFuncCallToStaticCallRectorTest
 */
final class RenameFuncCallToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTIONS_TO_STATIC_CALLS = '$functionsToStaticCalls';

    /**
     * @var string[][]
     */
    private $functionsToStaticCalls = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename func call to static call', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        strPee('...');
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        \Strings::strPaa('...');
    }
}
PHP

                , [
                    self::FUNCTIONS_TO_STATIC_CALLS => [
                        'strPee' => ['Strings', 'strPaa'],
                    ],
                ]),
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
        foreach ($this->functionsToStaticCalls as $function => $staticCall) {
            if (! $this->isName($node, $function)) {
                continue;
            }

            $staticCall = $this->createStaticCall($staticCall[0], $staticCall[1]);
            $staticCall->args = $node->args;

            return $staticCall;
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->functionsToStaticCalls = $configuration[self::FUNCTIONS_TO_STATIC_CALLS] ?? [];
        $this->ensureConfigurationIsValid($this->functionsToStaticCalls);
    }

    private function ensureConfigurationIsValid(array $functionsToStaticCalls): void
    {
        foreach ($functionsToStaticCalls as $function => $staticCall) {
            if (! is_string($function)) {
                throw new InvalidConfigurationException();
            }

            if (count($staticCall) !== 2) {
                throw new InvalidConfigurationException();
            }

            if (! is_string($staticCall[0])) {
                throw new InvalidConfigurationException();
            }

            if (! is_string($staticCall[1])) {
                throw new InvalidConfigurationException();
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Renaming\Tests\Rector\FuncCall\RenameFuncCallToStaticCallRector\RenameFuncCallToStaticCallRectorTest
 */
final class RenameFuncCallToStaticCallRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $functionsToStaticCalls = [];

    public function __construct(array $functionsToStaticCalls = [])
    {
        $this->functionsToStaticCalls = $functionsToStaticCalls;
        $this->ensureConfigurationIsValid($functionsToStaticCalls);
    }

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
                    '$functionsToStaticCalls' => [
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

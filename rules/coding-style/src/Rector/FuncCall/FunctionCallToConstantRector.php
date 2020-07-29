<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\FunctionCallToConstantRectorTest
 */
final class FunctionCallToConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTIONS_TO_CONSTANTS = '$functionsToConstants';

    /**
     * @var string[]
     */
    private $functionsToConstants = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes use of function calls to use constants', [
            new CodeSample(
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        $value = php_sapi_name();
    }
}
EOS
                ,
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        $value = PHP_SAPI;
    }
}
EOS
            ),
            new CodeSample(
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        $value = pi();
    }
}
EOS
                ,
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        $value = M_PI;
    }
}
EOS
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
        $functionName = $this->getName($node);
        if (! $functionName || ! array_key_exists($functionName, $this->functionsToConstants)) {
            return null;
        }

        return new ConstFetch(new Name($this->functionsToConstants[$functionName]));
    }

    public function configure(array $configuration): void
    {
        $this->functionsToConstants = $configuration[self::FUNCTIONS_TO_CONSTANTS] ?? [];
    }
}

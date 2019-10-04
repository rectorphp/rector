<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\FunctionCallToConstantRectorTest
 */
final class FunctionCallToConstantRector extends AbstractRector
{
    /**
     * @var string[]string
     */
    private $functionsToConstants;

    /**
     * @param string[] $functionsToConstants
     */
    public function __construct(array $functionsToConstants = [])
    {
        $this->functionsToConstants = $functionsToConstants;
    }

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
}

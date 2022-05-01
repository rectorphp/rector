<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FuncCall\FuncCallToConstFetchRector\FunctionCallToConstantRectorTest
 */
final class FuncCallToConstFetchRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private $functionsToConstants = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes use of function calls to use constants', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = php_sapi_name();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = PHP_SAPI;
    }
}
CODE_SAMPLE
, ['php_sapi_name' => 'PHP_SAPI'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $functionName = $this->getName($node);
        if (!\is_string($functionName)) {
            return null;
        }
        if (!\array_key_exists($functionName, $this->functionsToConstants)) {
            return null;
        }
        return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name($this->functionsToConstants[$functionName]));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220501\Webmozart\Assert\Assert::allString($configuration);
        \RectorPrefix20220501\Webmozart\Assert\Assert::allString(\array_keys($configuration));
        /** @var array<string, string> $configuration */
        $this->functionsToConstants = $configuration;
    }
}

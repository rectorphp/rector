<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Transform\ValueObject\MethodCallToReturn;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210514\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Expression\MethodCallToReturnRector\MethodCallToReturnRectorTest
 */
final class MethodCallToReturnRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALL_WRAPS = 'method_call_wraps';
    /**
     * @var MethodCallToReturn[]
     */
    private $methodCallWraps = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Wrap method call to return', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->deny();
    }

    public function deny()
    {
        return 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return $this->deny();
    }

    public function deny()
    {
        return 1;
    }
}
CODE_SAMPLE
, [self::METHOD_CALL_WRAPS => ['SomeClass' => ['deny']]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        $methodCall = $node->expr;
        return $this->refactorMethodCall($methodCall);
    }
    /**
     * @param array<string, MethodCallToReturn[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $methodCallWraps = $configuration[self::METHOD_CALL_WRAPS] ?? [];
        \RectorPrefix20210514\Webmozart\Assert\Assert::allIsInstanceOf($methodCallWraps, \Rector\Transform\ValueObject\MethodCallToReturn::class);
        $this->methodCallWraps = $methodCallWraps;
    }
    private function refactorMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $parent = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        foreach ($this->methodCallWraps as $methodCallWrap) {
            if (!$this->isObjectType($methodCall->var, $methodCallWrap->getObjectType())) {
                continue;
            }
            if (!$this->isName($methodCall->name, $methodCallWrap->getMethod())) {
                continue;
            }
            // already wrapped
            if ($parent instanceof \PhpParser\Node\Stmt\Return_) {
                continue;
            }
            return new \PhpParser\Node\Stmt\Return_($methodCall);
        }
        return null;
    }
}

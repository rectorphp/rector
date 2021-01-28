<?php

declare(strict_types=1);

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
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\Expression\MethodCallToReturnRector\MethodCallToReturnRectorTest
 */
final class MethodCallToReturnRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALL_WRAPS = 'method_call_wraps';

    /**
     * @var MethodCallToReturn[]
     */
    private $methodCallWraps = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Wrap method call to return', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
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
,
                <<<'CODE_SAMPLE'
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

            , [
                self::METHOD_CALL_WRAPS => [
                    'SomeClass' => ['deny'],
                ],
            ]),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $node->expr;

        return $this->refactorMethodCall($methodCall);
    }

    public function configure(array $configuration): void
    {
        $methodCallWraps = $configuration[self::METHOD_CALL_WRAPS] ?? [];
        Assert::allIsInstanceOf($methodCallWraps, MethodCallToReturn::class);
        $this->methodCallWraps = $methodCallWraps;
    }

    private function refactorMethodCall(MethodCall $methodCall): ?Node
    {
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);

        foreach ($this->methodCallWraps as $methodCallWrap) {
            if (! $this->isObjectType($methodCall->var, $methodCallWrap->getClass())) {
                continue;
            }

            if (! $this->isName($methodCall->name, $methodCallWrap->getMethod())) {
                continue;
            }

            // already wrapped
            if ($parent instanceof Return_) {
                continue;
            }

            $return = new Return_($methodCall);
            $methodCall->setAttribute(AttributeKey::PARENT_NODE, $return);

            return $return;
        }

        return null;
    }
}

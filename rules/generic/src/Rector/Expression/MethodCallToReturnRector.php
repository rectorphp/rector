<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\Expression\MethodCallToReturnRector\MethodCallToReturnRectorTest
 */
final class MethodCallToReturnRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_NAMES_BY_TYPE = 'method_names_by_type';

    /**
     * @var string[][]
     */
    private $methodNamesByType = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Wrap method call to return', [
            new ConfiguredCodeSample(
                <<<'PHP'
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
PHP
,
                <<<'PHP'
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
PHP

            , [
                self::METHOD_NAMES_BY_TYPE => [
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
        $this->methodNamesByType = $configuration[self::METHOD_NAMES_BY_TYPE] ?? [];
    }

    private function refactorMethodCall(MethodCall $methodCall): ?Node
    {
        foreach ($this->methodNamesByType as $methodType => $methodNames) {
            if (! $this->isObjectType($methodCall->var, $methodType)) {
                continue;
            }

            if (! $this->isNames($methodCall->name, $methodNames)) {
                continue;
            }

            $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);

            // already wrapped
            if ($parentNode instanceof Return_) {
                continue;
            }

            $return = new Return_($methodCall);
            $methodCall->setAttribute(AttributeKey::PARENT_NODE, $return);

            return $return;
        }

        return null;
    }
}

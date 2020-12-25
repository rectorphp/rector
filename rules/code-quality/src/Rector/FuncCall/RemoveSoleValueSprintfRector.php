<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\RemoveSoleValueSprintfRector\RemoveSoleValueSprintfRectorTest
 */
final class RemoveSoleValueSprintfRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove sprintf() wrapper if not needed', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = sprintf('%s', 'hi');

        $welcome = 'hello';
        $value = sprintf('%s', $welcome);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 'hi';

        $welcome = 'hello';
        $value = $welcome;
    }
}
CODE_SAMPLE
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
        if (! $this->isName($node, 'sprintf')) {
            return null;
        }

        if (count($node->args) !== 2) {
            return null;
        }

        $maskArgument = $node->args[0]->value;
        if (! $maskArgument instanceof String_) {
            return null;
        }

        if ($maskArgument->value !== '%s') {
            return null;
        }

        $valueArgument = $node->args[1]->value;
        if (! $this->isStaticType($valueArgument, StringType::class)) {
            return null;
        }

        return $valueArgument;
    }
}

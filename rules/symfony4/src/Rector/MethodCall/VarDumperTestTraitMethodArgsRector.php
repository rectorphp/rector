<?php

declare(strict_types=1);

namespace Rector\Symfony4\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Symfony4\Tests\Rector\MethodCall\VarDumperTestTraitMethodArgsRector\VarDumperTestTraitMethodArgsRectorTest
 */
final class VarDumperTestTraitMethodArgsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Adds a new `$filter` argument in `VarDumperTestTrait->assertDumpEquals()` and `VarDumperTestTrait->assertDumpMatchesFormat()` in Validator in Symfony.',
            [
                new CodeSample(
                    '$varDumperTestTrait->assertDumpEquals($dump, $data, $message = "");',
                    '$varDumperTestTrait->assertDumpEquals($dump, $data, $filter = 0, $message = "");'
                ),
                new CodeSample(
                    '$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $message = "");',
                    '$varDumperTestTrait->assertDumpMatchesFormat($dump, $data, $filter = 0, $message = "");'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->var, 'Symfony\Component\VarDumper\Test\VarDumperTestTrait')) {
            return null;
        }

        if (! $this->isNames($node->name, ['assertDumpEquals', 'assertDumpMatchesFormat'])) {
            return null;
        }
        if (count($node->args) <= 2) {
            return null;
        }
        if ($node->args[2]->value instanceof ConstFetch) {
            return null;
        }

        if ($node->args[2]->value instanceof String_) {
            $node->args[3] = $node->args[2];
            $node->args[2] = $this->nodeFactory->createArg(0);

            return $node;
        }

        return null;
    }
}

<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\VarDumper;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;

/**
 * @see \Rector\Symfony\Tests\Rector\VarDumper\VarDumperTestTraitMethodArgsRector\VarDumperTestTraitMethodArgsRectorTest
 */
final class VarDumperTestTraitMethodArgsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds new `$format` argument in `VarDumperTestTrait->assertDumpEquals()` in Validator in Symfony.',
            [
                new CodeSample(
                    '$varDumperTestTrait->assertDumpEquals($dump, $data, $mesage = "");',
                    '$varDumperTestTrait->assertDumpEquals($dump, $data, $context = null, $mesage = "");'
                ),
                new CodeSample(
                    '$varDumperTestTrait->assertDumpMatchesFormat($dump, $format, $mesage = "");',
                    '$varDumperTestTrait->assertDumpMatchesFormat($dump, $format, $context = null,  $mesage = "");'
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
        if (! $this->isObjectType($node->var, VarDumperTestTrait::class)) {
            return null;
        }

        if (! $this->isNames($node->name, ['assertDumpEquals', 'assertDumpMatchesFormat'])) {
            return null;
        }

        if (count($node->args) <= 2 || $node->args[2]->value instanceof ConstFetch) {
            return null;
        }

        if ($node->args[2]->value instanceof String_) {
            $node->args[3] = $node->args[2];
            $node->args[2] = $this->createArg($this->createNull());

            return $node;
        }

        return null;
    }
}

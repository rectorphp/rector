<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\VarDumper;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class VarDumperTestTraitMethodArgsRector extends AbstractRector
{
    /**
     * @var string
     */
    private $traitName;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        string $traitName = 'Symfony\Component\VarDumper\Test\VarDumperTestTrait'
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->traitName = $traitName;
    }

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
        if (! $this->isType($node, $this->traitName)) {
            return null;
        }

        if (! $this->isNames($node, ['assertDumpEquals', 'assertDumpMatchesFormat'])) {
            return null;
        }

        if (count($node->args) <= 2 || $node->args[2]->value instanceof ConstFetch) {
            return null;
        }

        if ($node->args[2]->value instanceof String_) {
            $node->args[3] = $node->args[2];
            $node->args[2] = $this->nodeFactory->createArg($this->createNull());

            return $node;
        }

        return null;
    }
}

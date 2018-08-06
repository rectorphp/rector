<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\VarDumper;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class VarDumperTestTraitMethodArgsRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;
    /**
     * @var string
     */
    private $traitName;

    public function __construct(
        MethodCallAnalyzer $methodCallAnalyzer,
        NodeFactory $nodeFactory,
        string $traitName = 'Symfony\Component\VarDumper\Test\VarDumperTestTrait'
    )
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
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

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethods(
            $node,
            $this->traitName,
            ['assertDumpEquals', 'assertDumpMatchesFormat']
        )) {
            return false;
        }

        /** @var StaticCall $staticCallNode */
        $staticCallNode = $node;

        if (count($staticCallNode->args) <= 2 || $staticCallNode->args[2]->value instanceof ConstFetch) {
            return false;
        }

        return true;
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodArguments = $node->args;

        if ($methodArguments[2]->value instanceof String_) {
            $methodArguments[3] = $methodArguments[2];
            $methodArguments[2] = $this->nodeFactory->createArg($this->nodeFactory->createNullConstant());

            $node->args = $methodArguments;

            return $node;
        }

        return null;
    }
}

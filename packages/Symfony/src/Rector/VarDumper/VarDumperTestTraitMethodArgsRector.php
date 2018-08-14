<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\VarDumper;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
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
    ) {
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethods(
            $methodCallNode,
            $this->traitName,
            ['assertDumpEquals', 'assertDumpMatchesFormat']
        )) {
            return null;
        }

        if (count($methodCallNode->args) <= 2 || $methodCallNode->args[2]->value instanceof ConstFetch) {
            return null;
        }

        if ($methodCallNode->args[2]->value instanceof String_) {
            $methodCallNode->args[3] = $methodCallNode->args[2];
            $methodCallNode->args[2] = $this->nodeFactory->createArg($this->nodeFactory->createNullConstant());

            return $methodCallNode;
        }

        return null;
    }
}

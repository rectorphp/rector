<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\VarDumper;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Converts all:
 * - VarDumperTestTrait->assertDumpEquals($dump, $data, $mesage = '');
 * - VarDumperTestTrait->assertDumpMatchesFormat($dump, $format, $mesage = '');
 *
 * into:
 * - VarDumperTestTrait->assertDumpEquals($dump, $data, $context = null, $mesage = '');
 * - VarDumperTestTrait->assertDumpMatchesFormat($dump, $format, $context = null,  $mesage = '');
 */
final class VarDumperTestTraitMethodArgsRector extends AbstractRector
{
    /**
     * @var string
     */
    private const TRAIT_NAME = 'Symfony\Component\VarDumper\Test\VarDumperTestTrait';

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethods(
            $node,
            self::TRAIT_NAME,
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

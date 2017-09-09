<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\Rector\Set\SetNames;

/**
 * Converts all:
 * VarDumperTestTrait::assertDumpEquals($dump, $data, $mesage = '');
 * VarDumperTestTrait::assertDumpMatchesFormat($dump, $format, $mesage = '');
 *
 * into:
 * VarDumperTestTrait::assertDumpEquals($dump, $data, $context = null, $mesage = '');
 * VarDumperTestTrait::assertDumpMatchesFormat($dump, $format, $context = null,  $mesage = '');
 */
final class VarDumperTestTraitMethodArgsRector extends AbstractRector
{
    /**
     * @var string
     */
    private const TRAIT_NAME = 'VarDumperTestTrait';

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

    public function getSetName(): string
    {
        return SetNames::SYMFONY;
    }

    public function sinceVersion(): float
    {
        return 4.0;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isStaticMethodCallTypeAndMethods(
            $node,
            self::TRAIT_NAME,
            ['assertDumpEquals', 'assertDumpMatchesFormat']
        )) {
            return false;
        }

        /** @var StaticCall $node */
        if (count($node->args) <= 2) {
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
            $methodArguments[2] = $this->nodeFactory->createNullConstant();

            $node->args = $methodArguments;

            return $node;
        }

        return null;
    }
}

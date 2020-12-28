<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\FuncCall\VersionCompareFuncCallToConstantRector\VersionCompareFuncCallToConstantRectorTest
 */
final class VersionCompareFuncCallToConstantRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const OPERATOR_TO_COMPARISON = [
        '=' => Identical::class,
        '==' => Identical::class,
        'eq' => Identical::class,
        '!=' => NotIdentical::class,
        '<>' => NotIdentical::class,
        'ne' => NotIdentical::class,
        '>' => Greater::class,
        'gt' => Greater::class,
        '<' => Smaller::class,
        'lt' => Smaller::class,
        '>=' => GreaterOrEqual::class,
        'ge' => GreaterOrEqual::class,
        '<=' => SmallerOrEqual::class,
        'le' => SmallerOrEqual::class,
    ];

    /**
     * @var string
     * @see https://regex101.com/r/yl9g25/1
     */
    private const SEMANTIC_VERSION_REGEX = '#^\d+\.\d+\.\d+$#';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes use of call to version compare function to use of PHP version constant',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        version_compare(PHP_VERSION, '5.3.0', '<');
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        PHP_VERSION_ID < 50300;
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
        if (! $this->isName($node, 'version_compare')) {
            return null;
        }

        if (count($node->args) !== 3) {
            return null;
        }

        if (! $this->isPhpVersionConstant($node->args[0]->value) && ! $this->isPhpVersionConstant(
            $node->args[1]->value
        )) {
            return null;
        }

        $left = $this->getNewNodeForArg($node->args[0]->value);
        $right = $this->getNewNodeForArg($node->args[1]->value);

        /** @var String_ $operator */
        $operator = $node->args[2]->value;
        $comparisonClass = self::OPERATOR_TO_COMPARISON[$operator->value];

        return new $comparisonClass($left, $right);
    }

    private function isPhpVersionConstant(Expr $expr): bool
    {
        if (! $expr instanceof ConstFetch) {
            return false;
        }
        return $expr->name->toString() === 'PHP_VERSION';
    }

    private function getNewNodeForArg(Expr $expr): Expr
    {
        if ($this->isPhpVersionConstant($expr)) {
            return new ConstFetch(new Name('PHP_VERSION_ID'));
        }

        return $this->getVersionNumberFormVersionString($expr);
    }

    private function getVersionNumberFormVersionString(Expr $expr): LNumber
    {
        if (! $expr instanceof String_) {
            throw new ShouldNotHappenException();
        }

        if (! Strings::match($expr->value, self::SEMANTIC_VERSION_REGEX)) {
            throw new ShouldNotHappenException();
        }

        $versionParts = explode('.', $expr->value);

        return new LNumber((int) $versionParts[0] * 10000 + (int) $versionParts[1] * 100 + (int) $versionParts[2]);
    }
}

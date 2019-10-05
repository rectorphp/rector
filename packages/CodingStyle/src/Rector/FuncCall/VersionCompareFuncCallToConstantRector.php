<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

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
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\FuncCall\FunctionCallToConstantRector\FunctionCallToConstantRectorTest
 */
final class VersionCompareFuncCallToConstantRector extends AbstractRector
{
    /**
     * @var string[]string
     */
    private $operatorToComparison = [
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes use of call to version compare function to use of PHP version constant', [
            new CodeSample(
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        version_compare(PHP_VERSION, '5.3.0', '<');
    }
}
EOS
                ,
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        PHP_VERSION_ID < 50300;
    }
}
EOS
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
        $comparison = $this->operatorToComparison[$operator->value];

        return new $comparison($left, $right);
    }

    private function isPhpVersionConstant(Expr $expr): bool
    {
        if ($expr instanceof ConstFetch && $expr->name->toString() === 'PHP_VERSION') {
            return true;
        }

        return false;
    }

    private function getNewNodeForArg(Expr $expr): Node
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

        if (! preg_match('#^\d+\.\d+\.\d+$#', $expr->value)) {
            throw new ShouldNotHappenException();
        }

        $versionParts = explode('.', $expr->value);

        return new LNumber((int) $versionParts[0] * 10000 + (int) $versionParts[1] * 100 + (int) $versionParts[2]);
    }
}

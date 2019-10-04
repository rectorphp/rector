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
final class FunctionCallToConstantRector extends AbstractRector
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
        return new RectorDefinition('Changes use of function calls to use constants', [
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
            new CodeSample(
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        $value = php_sapi_name();
    }
}
EOS
                ,
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        $value = PHP_SAPI;
    }
}
EOS
            ),
            new CodeSample(
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        $value = pi();
    }
}
EOS
                ,
                <<<'EOS'
class SomeClass
{
    public function run()
    {
        $value = M_PI;
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
        if ($this->isName($node, 'version_compare')) {
            return $this->refactorVersionCompare($node);
        }

        if ($this->isName($node, 'php_sapi_name')) {
            return new ConstFetch(new Name('PHP_SAPI'));
        }

        if ($this->isName($node, 'pi')) {
            return new ConstFetch(new Name('M_PI'));
        }

        return null;
    }

    private function refactorVersionCompare(Node $node): ?Node
    {
        if (count($node->args) !== 3) {
            return null;
        }

        if (!$this->isPhpVersionConstant($node->args[0]->value) && !$this->isPhpVersionConstant($node->args[1]->value)) {
            return null;
        }

        $left = $this->getNewNodeForArg($node->args[0]->value);
        $right = $this->getNewNodeForArg($node->args[1]->value);

        $comparison = $this->operatorToComparison[$node->args[2]->value->value];

        return new $comparison($left, $right);
    }

    private function isPhpVersionConstant(Expr $value): bool
    {
        if ($value instanceof ConstFetch && $value->name->toString() === 'PHP_VERSION') {
            return true;
        }

        return false;
    }

    private function getNewNodeForArg(Expr $value): Node
    {
        if ($this->isPhpVersionConstant($value)) {
            return new ConstFetch(new Name('PHP_VERSION_ID'));
        }

        return $this->getVersionNumberFormVersionString($value);
    }

    private function getVersionNumberFormVersionString(Expr $value): LNumber
    {
        if (! $value instanceof String_) {
            throw new ShouldNotHappenException();
        }

        if (! preg_match('/^\d+\.\d+\.\d+$/', $value->value)) {
            throw new ShouldNotHappenException();
        }

        $versionParts = explode('.', $value->value);

        return new LNumber((int) $versionParts[0] * 10000 + (int) $versionParts[1] * 100 + (int) $versionParts[2]);
    }
}

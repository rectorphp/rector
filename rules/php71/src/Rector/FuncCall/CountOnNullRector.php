<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\NullType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://3v4l.org/Bndc9
 *
 * @see \Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\CountOnNullRectorTest
 */
final class CountOnNullRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ALREADY_CHANGED_ON_COUNT = 'already_changed_on_count';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes count() on null to safe ternary check',
            [new CodeSample(
<<<'CODE_SAMPLE'
$values = null;
$count = count($values);
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$values = null;
$count = is_array($values) || $values instanceof Countable ? count($values) : 0;
CODE_SAMPLE
            )]
        );
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $countedNode = $node->args[0]->value;
        if ($this->isCountableType($countedNode)) {
            return null;
        }

        if ($this->isNullableType($countedNode) || $this->isStaticType($countedNode, NullType::class)) {
            $identical = new Identical($countedNode, $this->createNull());
            $ternaryNode = new Ternary($identical, new LNumber(0), $node);
        } else {
            if ($this->isAtLeastPhpVersion(PhpVersionFeature::IS_COUNTABLE)) {
                $conditionNode = new FuncCall(new Name('is_countable'), [new Arg($countedNode)]);
            } else {
                $instanceof = new Instanceof_($countedNode, new FullyQualified('Countable'));
                $conditionNode = new BooleanOr(
                    $this->createFuncCall('is_array', [new Arg($countedNode)]),
                    $instanceof
                );
            }

            $ternaryNode = new Ternary($conditionNode, $node, new LNumber(0));
        }

        // prevent infinity loop re-resolution
        $node->setAttribute(self::ALREADY_CHANGED_ON_COUNT, true);

        return $ternaryNode;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isName($funcCall, 'count')) {
            return true;
        }
        $alreadyChangedOnCount = $funcCall->getAttribute(self::ALREADY_CHANGED_ON_COUNT);

        // check if it has some condition before already, if so, probably it's already handled
        if ($alreadyChangedOnCount) {
            return true;
        }

        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Ternary) {
            return true;
        }

        if (! isset($funcCall->args[0])) {
            return true;
        }

        // skip node in trait, as impossible to analyse
        $classLike = $funcCall->getAttribute(AttributeKey::CLASS_NODE);

        return $classLike instanceof Trait_;
    }
}

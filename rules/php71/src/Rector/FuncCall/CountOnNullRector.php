<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\NullType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeAnalyzer\CountableTypeAnalyzer;
use Rector\Php71\NodeAnalyzer\CountableAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

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

    /**
     * @var CountableTypeAnalyzer
     */
    private $countableTypeAnalyzer;

    /**
     * @var CountableAnalyzer
     */
    private $countableAnalyzer;

    public function __construct(CountableTypeAnalyzer $countableTypeAnalyzer, CountableAnalyzer $countableAnalyzer)
    {
        $this->countableTypeAnalyzer = $countableTypeAnalyzer;
        $this->countableAnalyzer = $countableAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes count() on null to safe ternary check',
            [new CodeSample(
<<<'CODE_SAMPLE'
$values = null;
$count = count($values);
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$values = null;
$count = count((array) $values);
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
        if ($this->countableTypeAnalyzer->isCountableType($countedNode)) {
            return null;
        }

        // this can lead to false positive by phpstan, but that's best we can do
        $onlyValueType = $this->getStaticType($countedNode);
        if ($onlyValueType instanceof ArrayType) {
            if (! $this->countableAnalyzer->isCastableArrayType($countedNode)) {
                return null;
            }

            return $this->castToArray($countedNode, $node);
        }

        if ($this->nodeTypeResolver->isNullableArrayType($countedNode)) {
            return $this->castToArray($countedNode, $node);
        }

        if ($this->nodeTypeResolver->isNullableType($countedNode) || $this->isStaticType(
            $countedNode,
            NullType::class
        )) {
            $identical = new Identical($countedNode, $this->nodeFactory->createNull());
            $ternary = new Ternary($identical, new LNumber(0), $node);
            // prevent infinity loop re-resolution
            $node->setAttribute(self::ALREADY_CHANGED_ON_COUNT, true);
            return $ternary;
        }

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::IS_COUNTABLE)) {
            $conditionNode = new FuncCall(new Name('is_countable'), [new Arg($countedNode)]);
        } else {
            $instanceof = new Instanceof_($countedNode, new FullyQualified('Countable'));
            $conditionNode = new BooleanOr($this->nodeFactory->createFuncCall(
                'is_array',
                [new Arg($countedNode)]
            ), $instanceof);
        }

        // prevent infinity loop re-resolution
        $node->setAttribute(self::ALREADY_CHANGED_ON_COUNT, true);

        return new Ternary($conditionNode, $node, new LNumber(0));
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

    private function castToArray(Expr $countedExpr, FuncCall $funcCall): FuncCall
    {
        $castArray = new Array_($countedExpr);
        $funcCall->args = [new Arg($castArray)];

        return $funcCall;
    }
}

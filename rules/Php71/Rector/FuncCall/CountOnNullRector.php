<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeAnalyzer\CountableTypeAnalyzer;
use Rector\Php71\NodeAnalyzer\CountableAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/Bndc9
 *
 * @see \Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\CountOnNullRectorTest
 */
final class CountOnNullRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const ALREADY_CHANGED_ON_COUNT = 'already_changed_on_count';

    public function __construct(
        private readonly CountableTypeAnalyzer $countableTypeAnalyzer,
        private readonly CountableAnalyzer $countableAnalyzer
    ) {
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::COUNT_ON_NULL;
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
     * @return array<class-string<Node>>
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

        /** @var Arg $arg0 */
        $arg0 = $node->args[0];
        $countedNode = $arg0->value;
        if ($this->countableTypeAnalyzer->isCountableType($countedNode)) {
            return null;
        }

        // this can lead to false positive by phpstan, but that's best we can do
        $onlyValueType = $this->getType($countedNode);
        if ($onlyValueType instanceof ArrayType) {
            if (! $this->countableAnalyzer->isCastableArrayType($countedNode, $onlyValueType)) {
                return null;
            }

            return $this->castToArray($countedNode, $node);
        }

        if ($this->nodeTypeResolver->isNullableTypeOfSpecificType($countedNode, ArrayType::class)) {
            return $this->castToArray($countedNode, $node);
        }

        $countedType = $this->getType($countedNode);

        if ($this->isAlwaysIterableType($countedType)) {
            return null;
        }

        if ($this->nodeTypeResolver->isNullableType($countedNode) || $countedType instanceof NullType) {
            $identical = new Identical($countedNode, $this->nodeFactory->createNull());
            $ternary = new Ternary($identical, new LNumber(0), $node);
            // prevent infinity loop re-resolution
            $node->setAttribute(self::ALREADY_CHANGED_ON_COUNT, true);
            return $ternary;
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::IS_COUNTABLE)) {
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

    private function isAlwaysIterableType(Type $possibleUnionType): bool
    {
        if (! $possibleUnionType instanceof UnionType) {
            return false;
        }

        $types = $possibleUnionType->getTypes();

        foreach ($types as $type) {
            if ($type->isIterable()->no()) {
                return false;
            }
        }

        return true;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        if (! $this->isName($funcCall, 'count')) {
            return true;
        }

        if (! isset($funcCall->args[0])) {
            return true;
        }

        if (! $funcCall->args[0] instanceof Arg) {
            return true;
        }

        if ($funcCall->args[0]->value instanceof ClassConstFetch) {
            return true;
        }

        $alreadyChangedOnCount = (bool) $funcCall->getAttribute(self::ALREADY_CHANGED_ON_COUNT, false);

        // check if it has some condition before already, if so, probably it's already handled
        if ($alreadyChangedOnCount) {
            return true;
        }

        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Ternary) {
            return true;
        }

        // skip node in trait, as impossible to analyse
        $trait = $this->betterNodeFinder->findParentType($funcCall, Trait_::class);
        return $trait instanceof Trait_;
    }

    private function castToArray(Expr $countedExpr, FuncCall $funcCall): FuncCall
    {
        $castArray = new Array_($countedExpr);
        $funcCall->args = [new Arg($castArray)];

        return $funcCall;
    }
}

<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\NodeManipulator\BinaryOpManipulator;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php83\Rector\BooleanAnd\JsonValidateRector\JsonValidateRectorTest
 */
final class JsonValidateRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @readonly
     */
    private BinaryOpManipulator $binaryOpManipulator;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    private const JSON_MAX_DEPTH = 0x7fffffff;
    public function __construct(BinaryOpManipulator $binaryOpManipulator, ValueResolver $valueResolver)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::JSON_VALIDATE;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace json_decode($json, true) !== null && json_last_error() === JSON_ERROR_NONE  with json_validate()', [new CodeSample(<<<'CODE_SAMPLE'
if (json_decode($json, true) !== null && json_last_error() === JSON_ERROR_NONE) {
}

CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (json_validate($json)) {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanAnd::class];
    }
    /**
     * @param BooleanAnd $node
     */
    public function refactor(Node $node): ?Node
    {
        $funcCall = $this->matchJsonValidateArg($node);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        $args = $funcCall->getArgs();
        if (count($args) < 1) {
            return null;
        }
        if (!$this->validateArgs($funcCall)) {
            return null;
        }
        $funcCall->name = new Name('json_validate');
        $funcCall->args = $args;
        return $funcCall;
    }
    public function providePolyfillPackage(): string
    {
        return PolyfillPackage::PHP_83;
    }
    public function matchJsonValidateArg(BooleanAnd $booleanAnd): ?FuncCall
    {
        // match: json_decode(...) !== null   OR   null !== json_decode(...)
        if (!$booleanAnd->left instanceof NotIdentical) {
            return null;
        }
        $decodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($booleanAnd->left, fn(Node $node): bool => $node instanceof FuncCall && $this->isName($node->name, 'json_decode'), fn(Node $node): bool => $node instanceof ConstFetch && $this->isName($node->name, 'null'));
        if (!$decodeMatch instanceof TwoNodeMatch) {
            return null;
        }
        // match: json_last_error() === JSON_ERROR_NONE   OR   JSON_ERROR_NONE === json_last_error()
        if (!$booleanAnd->right instanceof Identical) {
            return null;
        }
        $errorMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($booleanAnd->right, fn(Node $node): bool => $node instanceof FuncCall && $this->isName($node->name, 'json_last_error'), fn(Node $node): bool => $node instanceof ConstFetch && $this->isName($node->name, 'JSON_ERROR_NONE'));
        if (!$errorMatch instanceof TwoNodeMatch) {
            return null;
        }
        // always return the json_decode(...) call
        $expr = $decodeMatch->getFirstExpr();
        if (!$expr instanceof FuncCall) {
            return null;
        }
        return $expr;
    }
    private function validateArgs(FuncCall $funcCall): bool
    {
        $depth = $funcCall->getArg('depth', 2);
        $flags = $funcCall->getArg('flags', 3);
        if ($flags instanceof Arg) {
            $flagsValue = $this->valueResolver->getValue($flags);
            if ($flagsValue !== \JSON_INVALID_UTF8_IGNORE) {
                return \false;
            }
        }
        if ($depth instanceof Arg) {
            $depthValue = $this->valueResolver->getValue($depth);
            if ($depthValue <= 0 || $depthValue > self::JSON_MAX_DEPTH) {
                return \false;
            }
        }
        return \true;
    }
}

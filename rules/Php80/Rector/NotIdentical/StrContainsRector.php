<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\NotIdentical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Php80\NodeResolver\StrFalseComparisonResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
/**
 * @see \Rector\Tests\Php80\Rector\NotIdentical\StrContainsRector\StrContainsRectorTest
 */
final class StrContainsRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    /**
     * @readonly
     */
    private StrFalseComparisonResolver $strFalseComparisonResolver;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var string[]
     */
    private const OLD_STR_NAMES = ['strpos', 'strstr'];
    public function __construct(StrFalseComparisonResolver $strFalseComparisonResolver, ValueResolver $valueResolver)
    {
        $this->strFalseComparisonResolver = $strFalseComparisonResolver;
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_80;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace strpos() !== false and strstr()  with str_contains()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return strpos('abc', 'a') !== false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return str_contains('abc', 'a');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class, Equal::class, NotEqual::class];
    }
    /**
     * @param Identical|NotIdentical|Equal|NotEqual $node
     */
    public function refactor(Node $node): ?Node
    {
        $funcCall = $this->strFalseComparisonResolver->resolve($node, self::OLD_STR_NAMES);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        $args = $funcCall->getArgs();
        if (isset($args[1]) && $this->isEmptyStringNeedle($args[1]->value)) {
            return null;
        }
        if (isset($args[2])) {
            // str_contains() has no offset, and substr() would copy the haystack on every call
            if ($this->isName($funcCall->name, 'strpos') && !$this->valueResolver->isValue($args[2]->value, 0)) {
                return null;
            }
            unset($funcCall->args[2]);
        }
        $funcCall->name = new Name('str_contains');
        if ($node instanceof Identical || $node instanceof Equal) {
            return new BooleanNot($funcCall);
        }
        return $funcCall;
    }
    public function providePolyfillPackage(): string
    {
        return PolyfillPackage::PHP_80;
    }
    private function isEmptyStringNeedle(Expr $expr): bool
    {
        if (!$expr instanceof String_) {
            return \false;
        }
        return $expr->value === '';
    }
}

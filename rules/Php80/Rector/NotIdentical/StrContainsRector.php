<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\NotIdentical;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use Rector\Php80\NodeResolver\StrFalseComparisonResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
     * @var string[]
     */
    private const OLD_STR_NAMES = ['strpos', 'strstr'];
    public function __construct(StrFalseComparisonResolver $strFalseComparisonResolver)
    {
        $this->strFalseComparisonResolver = $strFalseComparisonResolver;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::STR_CONTAINS;
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
        if (isset($funcCall->getArgs()[2])) {
            $secondArg = $funcCall->getArgs()[2];
            if ($this->isName($funcCall->name, 'strpos') && !$this->isIntegerZero($secondArg->value)) {
                $funcCall->args[0] = new Arg($this->nodeFactory->createFuncCall('substr', [$funcCall->args[0], $secondArg]));
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
    private function isIntegerZero(Expr $expr): bool
    {
        if (!$expr instanceof Int_) {
            return \false;
        }
        return $expr->value === 0;
    }
}

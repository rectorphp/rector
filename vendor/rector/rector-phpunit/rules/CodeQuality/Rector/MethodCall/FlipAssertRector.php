<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\FlipAssertRector\FlipAssertRectorTest
 */
final class FlipAssertRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @var string[]
     */
    private const METHOD_NAMES = ['assertSame', 'assertNotSame', 'assertNotEquals', 'assertEquals', 'assertStringContainsString', 'assertEqualsCanonicalizing', 'assertNotEqualsCanonicalizing', 'assertEqualsIgnoringCase', 'assertNotEqualsIgnoringCase', 'assertEqualsWithDelta', 'assertNotEqualsWithDelta'];
    /**
     * @var array<class-string<Expr>>
     */
    private const COMPUTED_EXPR_CLASSES = [MethodCall::class, StaticCall::class, FuncCall::class, PropertyFetch::class, StaticPropertyFetch::class, NullsafeMethodCall::class, NullsafePropertyFetch::class, ArrayDimFetch::class];
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns accidentally flipped assert order to right one, with expected expr to left', [new CodeSample(<<<'CODE_SAMPLE'
<?php

use PHPUnit\Framework\TestCase;
class SomeTest extends TestCase
{
    public function test()
    {
        $result = '...';
        $this->assertSame($result, 'expected');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

use PHPUnit\Framework\TestCase;
class SomeTest extends TestCase
{
    public function test()
    {
        $result = '...';
        $this->assertSame('expected', $result);
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, self::METHOD_NAMES)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $secondArg = $node->getArgs()[1];
        // named args keep their meaning regardless of position
        if ($firstArg->name instanceof Identifier || $secondArg->name instanceof Identifier) {
            return null;
        }
        if (!$this->isFlipNeeded($firstArg->value, $secondArg->value)) {
            return null;
        }
        $oldArgs = $node->getArgs();
        // flip args
        [$oldArgs[0], $oldArgs[1]] = [$oldArgs[1], $oldArgs[0]];
        $node->args = $oldArgs;
        return $node;
    }
    private function isFlipNeeded(Expr $firstExpr, Expr $secondExpr): bool
    {
        // correct location
        if ($this->isExpectedValue($firstExpr)) {
            return \false;
        }
        if ($this->isExpectedValue($secondExpr)) {
            return \true;
        }
        // e.g. assertSame($obj->getValues(), [$a, $b])
        return $secondExpr instanceof Array_ && $this->isComputedExpr($firstExpr) && $this->isLiteralArray($secondExpr, \true);
    }
    /**
     * @param bool $allowVariables accept variables as array keys and values, not inside concat or new
     */
    private function isExpectedValue(Expr $expr, bool $allowVariables = \false): bool
    {
        if ($this->isScalarValue($expr) || $this->isEnumCaseValue($expr)) {
            return \true;
        }
        if ($allowVariables && $expr instanceof Variable) {
            return \true;
        }
        if ($expr instanceof New_) {
            return $this->isNewWithLiteralArgs($expr);
        }
        if ($expr instanceof Concat) {
            return $this->isExpectedValue($expr->left) && $this->isExpectedValue($expr->right);
        }
        return $expr instanceof Array_ && $this->isLiteralArray($expr, $allowVariables);
    }
    private function isNewWithLiteralArgs(New_ $new): bool
    {
        if (!$new->class instanceof Name) {
            return \false;
        }
        $found = \true;
        foreach ($new->getArgs() as $arg) {
            if (!(!$arg->unpack && !$arg->name instanceof Identifier && $this->isExpectedValue($arg->value))) {
                $found = \false;
                break;
            }
        }
        return $found;
    }
    private function isLiteralArray(Array_ $array, bool $allowVariables = \false): bool
    {
        $found = \true;
        foreach ($array->items as $arrayItem) {
            if (!(!$arrayItem->unpack && (!$arrayItem->key instanceof Expr || $this->isExpectedValue($arrayItem->key, $allowVariables)) && $this->isExpectedValue($arrayItem->value, $allowVariables))) {
                $found = \false;
                break;
            }
        }
        return $found;
    }
    private function isComputedExpr(Expr $expr): bool
    {
        $found = \false;
        foreach (self::COMPUTED_EXPR_CLASSES as $computedExprClass) {
            if ($expr instanceof $computedExprClass) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    /**
     * e.g. Status::Active->value, Status::Active->name
     */
    private function isEnumCaseValue(Expr $expr): bool
    {
        if (!$expr instanceof PropertyFetch || !$this->isNames($expr->name, ['value', 'name'])) {
            return \false;
        }
        return $expr->var instanceof ClassConstFetch && !$this->isName($expr->var->name, 'class');
    }
    /**
     * Scalar or constant, optionally negated, e.g. 'value', -1, PHP_INT_MAX, -self::LIMIT
     */
    private function isScalarValue(Expr $expr): bool
    {
        if ($expr instanceof UnaryMinus) {
            return $this->isScalarValue($expr->expr);
        }
        return $expr instanceof Scalar || $expr instanceof ConstFetch || $expr instanceof ClassConstFetch;
    }
}

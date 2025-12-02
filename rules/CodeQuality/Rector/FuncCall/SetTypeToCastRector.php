<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\SetTypeToCastRector\SetTypeToCastRectorTest
 */
final class SetTypeToCastRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var array<string, class-string<Cast>>
     */
    private const TYPE_TO_CAST = ['array' => Array_::class, 'bool' => Bool_::class, 'boolean' => Bool_::class, 'double' => Double::class, 'float' => Double::class, 'int' => Int_::class, 'integer' => Int_::class, 'object' => Object_::class, 'string' => String_::class];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `settype()` to `(type)` on standalone line. `settype()` returns always success/failure bool value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($foo)
    {
        settype($foo, 'string');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($foo)
    {
        $foo = (string) $foo;
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Stmt\Expression
    {
        // skip expr that are not standalone line, as settype() returns success bool value
        // and cannot be casted
        if (!$node->expr instanceof FuncCall) {
            return null;
        }
        $assign = $this->refactorFuncCall($node->expr);
        if (!$assign instanceof Assign) {
            return null;
        }
        return new Expression($assign);
    }
    private function refactorFuncCall(FuncCall $funcCall): ?\PhpParser\Node\Expr\Assign
    {
        if (!$this->isName($funcCall, 'setType')) {
            return null;
        }
        if ($funcCall->isFirstClassCallable()) {
            return null;
        }
        if (count($funcCall->getArgs()) < 2) {
            return null;
        }
        $secondArg = $funcCall->getArgs()[1];
        $typeValue = $this->valueResolver->getValue($secondArg->value);
        if (!is_string($typeValue)) {
            return null;
        }
        $typeValue = strtolower($typeValue);
        $firstArg = $funcCall->getArgs()[0];
        $variable = $firstArg->value;
        if (isset(self::TYPE_TO_CAST[$typeValue])) {
            $castClass = self::TYPE_TO_CAST[$typeValue];
            $castNode = new $castClass($variable);
            return new Assign($variable, $castNode);
        }
        if ($typeValue === 'null') {
            return new Assign($variable, $this->nodeFactory->createNull());
        }
        return null;
    }
}

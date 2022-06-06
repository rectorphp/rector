<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\Rector\StaticCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Ternary;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\StaticCall\DowngradePhpTokenRector\DowngradePhpTokenRectorTest
 */
final class DowngradePhpTokenRector extends AbstractRector
{
    /**
     * @var string
     */
    private const PHP_TOKEN = 'PhpToken';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('"something()" will be renamed to "somethingElse()"', [new CodeSample(<<<'CODE_SAMPLE'
$tokens = \PhpToken::tokenize($code);

foreach ($tokens as $phpToken) {
   $name = $phpToken->getTokenName();
   $text = $phpToken->text;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$tokens = token_get_all($code);

foreach ($tokens as $token) {
    $name = is_array($token) ? token_name($token[0]) : null;
    $text = is_array($token) ? $token[1] : $token;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class, MethodCall::class, PropertyFetch::class];
    }
    /**
     * @param StaticCall|MethodCall|PropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }
        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }
        return $this->refactorPropertyFetch($node);
    }
    private function refactorStaticCall(StaticCall $staticCall) : ?FuncCall
    {
        if (!$this->isObjectType($staticCall->class, new ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if (!$this->isName($staticCall->name, 'tokenize')) {
            return null;
        }
        return new FuncCall(new Name('token_get_all'), $staticCall->args);
    }
    private function refactorMethodCall(MethodCall $methodCall) : ?Ternary
    {
        if (!$this->isObjectType($methodCall->var, new ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'getTokenName')) {
            return null;
        }
        $isArrayFuncCall = new FuncCall(new Name('is_array'), [new Arg($methodCall->var)]);
        $arrayDimFetch = new ArrayDimFetch($methodCall->var, new LNumber(0));
        $tokenGetNameFuncCall = new FuncCall(new Name('token_name'), [new Arg($arrayDimFetch)]);
        return new Ternary($isArrayFuncCall, $tokenGetNameFuncCall, $this->nodeFactory->createNull());
    }
    private function refactorPropertyFetch(PropertyFetch $propertyFetch) : ?Ternary
    {
        if (!$this->isObjectType($propertyFetch->var, new ObjectType(self::PHP_TOKEN))) {
            return null;
        }
        if (!$this->isName($propertyFetch->name, 'text')) {
            return null;
        }
        $isArrayFuncCall = new FuncCall(new Name('is_array'), [new Arg($propertyFetch->var)]);
        $arrayDimFetch = new ArrayDimFetch($propertyFetch->var, new LNumber(1));
        return new Ternary($isArrayFuncCall, $arrayDimFetch, $propertyFetch->var);
    }
}

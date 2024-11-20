<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ObjectType;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Symfony\CodeQuality\Enum\ResponseClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\MethodCall\AssertSameResponseCodeWithDebugContentsRector\AssertSameResponseCodeWithDebugContentsRectorTest
 */
final class AssertSameResponseCodeWithDebugContentsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make assertSame(200, $response->getStatusCode()) in tests comparing response code to include response contents for faster feedback', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function run()
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $this->processResult();

        $this->assertSame(200, $response->getStatusCode());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeClass extends TestCase
{
    public function run()
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $this->processResult();

        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'assertSame')) {
            return null;
        }
        // there cannot be any custom message
        $args = $node->getArgs();
        if (\count($args) !== 2) {
            return null;
        }
        $firstArg = $args[0];
        $comparedValueType = $this->getType($firstArg->value);
        // must be number
        if (!$comparedValueType instanceof ConstantIntegerType) {
            return null;
        }
        $responseExpr = $this->matchResponseExpr($args[1]->value);
        if (!$responseExpr instanceof Expr) {
            return null;
        }
        $getContentMethodCall = new MethodCall($responseExpr, 'getContent');
        $node->args[2] = new Arg($getContentMethodCall);
        return $node;
    }
    /**
     * We look for $response->getStatusCode()
     * $client->getResponse()->getStatusCode()
     *
     * etc.
     */
    private function matchResponseExpr(Expr $expr) : ?Expr
    {
        if (!$expr instanceof MethodCall) {
            return null;
        }
        $varType = $this->nodeTypeResolver->getType($expr->var);
        if (!$varType instanceof ObjectType) {
            return null;
        }
        if (!$varType->isInstanceof(ResponseClass::BASIC)->yes()) {
            return null;
        }
        // must be status method call
        if (!$this->isName($expr->name, 'getStatusCode')) {
            return null;
        }
        return $expr->var;
    }
}

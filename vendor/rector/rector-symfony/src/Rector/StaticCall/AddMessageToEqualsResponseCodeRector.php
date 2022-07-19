<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\StaticCall\AddMessageToEqualsResponseCodeRector\AddMessageToEqualsResponseCodeRectorTest
 */
final class AddMessageToEqualsResponseCodeRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add response content to response code assert, so it is easier to debug', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class SomeClassTest extends TestCase
{
    public function test(Response $response)
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class SomeClassTest extends TestCase
{
    public function test(Response $response)
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
            $response->getContent()
        );
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
        return [StaticCall::class, MethodCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'assertEquals')) {
            return null;
        }
        // already has 3rd "message" argument
        if (isset($node->args[2])) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        if (!$this->isHttpRequestArgument($firstArg->value)) {
            return null;
        }
        $secondArg = $node->args[1];
        if (!$secondArg instanceof Arg) {
            return null;
        }
        $expr = $this->getParentOfGetStatusCode($secondArg->value);
        if (!$expr instanceof Expr) {
            return null;
        }
        $getContentMethodCall = new MethodCall($expr, 'getContent');
        $node->args[2] = new Arg($getContentMethodCall);
        return $node;
    }
    /**
     * $this->assertX(Response::SOME_STATUS)
     */
    private function isHttpRequestArgument(Expr $expr) : bool
    {
        if (!$expr instanceof ClassConstFetch) {
            return \false;
        }
        return $this->isObjectType($expr->class, new ObjectType('Symfony\\Component\\HttpFoundation\\Response'));
    }
    /**
     * @return Variable|MethodCall|Expr|null
     */
    private function getParentOfGetStatusCode(Expr $expr) : ?Node
    {
        $currentNode = $expr;
        while ($currentNode instanceof MethodCall) {
            if ($this->isName($currentNode->name, 'getStatusCode')) {
                return $currentNode->var;
            }
            $currentNode = $currentNode->var;
        }
        return null;
    }
}

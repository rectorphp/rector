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
final class AddMessageToEqualsResponseCodeRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add response content to response code assert, so it is easier to debug', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node->name, 'assertEquals')) {
            return null;
        }
        // already has 3rd "message" argument
        if (isset($node->args[2])) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if (!$this->isHttpRequestArgument($firstArg->value)) {
            return null;
        }
        $secondArg = $node->args[1];
        if (!$secondArg instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $parentVariable = $this->getParentOfGetStatusCode($secondArg->value);
        if (!$parentVariable instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $getContentMethodCall = new \PhpParser\Node\Expr\MethodCall($parentVariable, 'getContent');
        $node->args[2] = new \PhpParser\Node\Arg($getContentMethodCall);
        return $node;
    }
    /**
     * $this->assertX(Response::SOME_STATUS)
     */
    private function isHttpRequestArgument(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return \false;
        }
        return $this->isObjectType($expr->class, new \PHPStan\Type\ObjectType('Symfony\\Component\\HttpFoundation\\Response'));
    }
    /**
     * @return Variable|MethodCall|Expr|null
     */
    private function getParentOfGetStatusCode(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node
    {
        $currentNode = $expr;
        while ($currentNode instanceof \PhpParser\Node\Expr\MethodCall) {
            if ($this->isName($currentNode->name, 'getStatusCode')) {
                return $currentNode->var;
            }
            $currentNode = $currentNode->var;
        }
        return null;
    }
}

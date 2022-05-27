<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\BinaryOp\ResponseStatusCodeRector\ResponseStatusCodeRectorTest
 */
final class ResponseStatusCodeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $responseObjectType;
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer
     */
    private $literalCallLikeConstFetchReplacer;
    public function __construct(\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer $controllerAnalyzer, \Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer $literalCallLikeConstFetchReplacer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->literalCallLikeConstFetchReplacer = $literalCallLikeConstFetchReplacer;
        $this->responseObjectType = new \PHPStan\Type\ObjectType('Symfony\\Component\\HttpFoundation\\Response');
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns status code numbers to constants', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Response;

class SomeController
{
    public function index()
    {
        $response = new Response();
        $response->setStatusCode(200);

        if ($response->getStatusCode() === 200) {
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Response;

class SomeController
{
    public function index()
    {
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);

        if ($response->getStatusCode() === Response::HTTP_OK) {
        }
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
        return [\PhpParser\Node\Expr\BinaryOp::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param BinaryOp|MethodCall|New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\New_) {
            return $this->processNew($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->processMethodCall($node);
        }
        return $this->processBinaryOp($node);
    }
    /**
     * @return \PhpParser\Node\Expr\CallLike|null
     */
    private function processMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        if ($this->isName($methodCall->name, 'assert*')) {
            return $this->processAssertMethodCall($methodCall);
        }
        if ($this->isName($methodCall->name, 'redirect')) {
            return $this->processRedirectMethodCall($methodCall);
        }
        if (!$this->isObjectType($methodCall->var, $this->responseObjectType)) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'setStatusCode')) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, 'Symfony\\Component\\HttpFoundation\\Response', \Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    private function processBinaryOp(\PhpParser\Node\Expr\BinaryOp $binaryOp) : ?\PhpParser\Node\Expr\BinaryOp
    {
        if (!$this->isGetStatusMethod($binaryOp->left) && !$this->isGetStatusMethod($binaryOp->right)) {
            return null;
        }
        if ($binaryOp->right instanceof \PhpParser\Node\Scalar\LNumber && $this->isGetStatusMethod($binaryOp->left)) {
            $binaryOp->right = $this->convertNumberToConstant($binaryOp->right);
            return $binaryOp;
        }
        if ($binaryOp->left instanceof \PhpParser\Node\Scalar\LNumber && $this->isGetStatusMethod($binaryOp->right)) {
            $binaryOp->left = $this->convertNumberToConstant($binaryOp->left);
            return $binaryOp;
        }
        return null;
    }
    private function isGetStatusMethod(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$this->isObjectType($node->var, $this->responseObjectType)) {
            return \false;
        }
        return $this->isName($node->name, 'getStatusCode');
    }
    /**
     * @return \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Scalar\LNumber
     */
    private function convertNumberToConstant(\PhpParser\Node\Scalar\LNumber $lNumber)
    {
        if (!isset(\Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap::CODE_TO_CONST[$lNumber->value])) {
            return $lNumber;
        }
        return $this->nodeFactory->createClassConstFetch($this->responseObjectType->getClassName(), \Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap::CODE_TO_CONST[$lNumber->value]);
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function processAssertMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        $args = $methodCall->getArgs();
        if (!isset($args[1])) {
            return null;
        }
        $secondArg = $args[1];
        if (!$this->isGetStatusMethod($secondArg->value)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, 'Symfony\\Component\\HttpFoundation\\Response', \Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function processRedirectMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        if (!$this->controllerAnalyzer->isController($methodCall->var)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 1, 'Symfony\\Component\\HttpFoundation\\Response', \Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    /**
     * @return \PhpParser\Node\Expr\New_|null
     */
    private function processNew(\PhpParser\Node\Expr\New_ $new)
    {
        if (!$this->isObjectType($new->class, $this->responseObjectType)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($new, 1, 'Symfony\\Component\\HttpFoundation\\Response', \Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap::CODE_TO_CONST);
    }
}

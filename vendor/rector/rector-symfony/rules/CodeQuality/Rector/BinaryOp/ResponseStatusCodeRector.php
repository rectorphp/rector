<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\Int_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\CodeQuality\Enum\ResponseClass;
use Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\BinaryOp\ResponseStatusCodeRector\ResponseStatusCodeRectorTest
 */
final class ResponseStatusCodeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ControllerAnalyzer $controllerAnalyzer;
    /**
     * @readonly
     */
    private LiteralCallLikeConstFetchReplacer $literalCallLikeConstFetchReplacer;
    /**
     * @readonly
     */
    private ObjectType $responseObjectType;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, LiteralCallLikeConstFetchReplacer $literalCallLikeConstFetchReplacer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->literalCallLikeConstFetchReplacer = $literalCallLikeConstFetchReplacer;
        $this->responseObjectType = new ObjectType(ResponseClass::BASIC);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns status code numbers to constants', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [BinaryOp::class, MethodCall::class, New_::class];
    }
    /**
     * @param BinaryOp|MethodCall|New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof New_) {
            return $this->processNew($node);
        }
        if ($node instanceof MethodCall) {
            return $this->processMethodCall($node);
        }
        return $this->processBinaryOp($node);
    }
    private function processMethodCall(MethodCall $methodCall) : ?\PhpParser\Node\Expr\CallLike
    {
        $methodCallName = $this->getName($methodCall->name);
        if (!\is_string($methodCallName)) {
            return null;
        }
        if (\strncmp($methodCallName, 'assert', \strlen('assert')) === 0) {
            return $this->processAssertMethodCall($methodCall);
        }
        if ($methodCallName === 'redirect') {
            return $this->processRedirectMethodCall($methodCall);
        }
        if ($methodCallName !== 'setStatusCode') {
            return null;
        }
        if (!$this->isObjectType($methodCall->var, $this->responseObjectType)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, ResponseClass::BASIC, SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    private function processBinaryOp(BinaryOp $binaryOp) : ?BinaryOp
    {
        if (!$this->isGetStatusMethod($binaryOp->left) && !$this->isGetStatusMethod($binaryOp->right)) {
            return null;
        }
        if ($binaryOp->right instanceof Int_ && $this->isGetStatusMethod($binaryOp->left)) {
            $binaryOp->right = $this->convertNumberToConstant($binaryOp->right);
            return $binaryOp;
        }
        if ($binaryOp->left instanceof Int_ && $this->isGetStatusMethod($binaryOp->right)) {
            $binaryOp->left = $this->convertNumberToConstant($binaryOp->left);
            return $binaryOp;
        }
        return null;
    }
    private function isGetStatusMethod(Node $node) : bool
    {
        if (!$node instanceof MethodCall) {
            return \false;
        }
        if (!$this->isName($node->name, 'getStatusCode')) {
            return \false;
        }
        return $this->isObjectType($node->var, $this->responseObjectType);
    }
    /**
     * @return \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Scalar\Int_
     */
    private function convertNumberToConstant(Int_ $int)
    {
        if (!isset(SymfonyResponseConstantMap::CODE_TO_CONST[$int->value])) {
            return $int;
        }
        return $this->nodeFactory->createClassConstFetch($this->responseObjectType->getClassName(), SymfonyResponseConstantMap::CODE_TO_CONST[$int->value]);
    }
    private function processAssertMethodCall(MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        $args = $methodCall->getArgs();
        if (!isset($args[1])) {
            return null;
        }
        $secondArg = $args[1];
        if (!$this->isGetStatusMethod($secondArg->value)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, ResponseClass::BASIC, SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    private function processRedirectMethodCall(MethodCall $methodCall) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->controllerAnalyzer->isController($methodCall->var)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 1, ResponseClass::BASIC, SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    private function processNew(New_ $new) : ?\PhpParser\Node\Expr\New_
    {
        if (!$this->isObjectType($new->class, $this->responseObjectType)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($new, 1, ResponseClass::BASIC, SymfonyResponseConstantMap::CODE_TO_CONST);
    }
}

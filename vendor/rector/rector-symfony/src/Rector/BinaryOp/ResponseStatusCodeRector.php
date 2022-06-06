<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\BinaryOp;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp;
use RectorPrefix20220606\PhpParser\Node\Expr\CallLike;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer;
use RectorPrefix20220606\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use RectorPrefix20220606\Rector\Symfony\ValueObject\ConstantMap\SymfonyResponseConstantMap;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\BinaryOp\ResponseStatusCodeRector\ResponseStatusCodeRectorTest
 */
final class ResponseStatusCodeRector extends AbstractRector
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
    public function __construct(ControllerAnalyzer $controllerAnalyzer, LiteralCallLikeConstFetchReplacer $literalCallLikeConstFetchReplacer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->literalCallLikeConstFetchReplacer = $literalCallLikeConstFetchReplacer;
        $this->responseObjectType = new ObjectType('Symfony\\Component\\HttpFoundation\\Response');
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
    /**
     * @return \PhpParser\Node\Expr\CallLike|null
     */
    private function processMethodCall(MethodCall $methodCall)
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
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, 'Symfony\\Component\\HttpFoundation\\Response', SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    private function processBinaryOp(BinaryOp $binaryOp) : ?BinaryOp
    {
        if (!$this->isGetStatusMethod($binaryOp->left) && !$this->isGetStatusMethod($binaryOp->right)) {
            return null;
        }
        if ($binaryOp->right instanceof LNumber && $this->isGetStatusMethod($binaryOp->left)) {
            $binaryOp->right = $this->convertNumberToConstant($binaryOp->right);
            return $binaryOp;
        }
        if ($binaryOp->left instanceof LNumber && $this->isGetStatusMethod($binaryOp->right)) {
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
        if (!$this->isObjectType($node->var, $this->responseObjectType)) {
            return \false;
        }
        return $this->isName($node->name, 'getStatusCode');
    }
    /**
     * @return \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Scalar\LNumber
     */
    private function convertNumberToConstant(LNumber $lNumber)
    {
        if (!isset(SymfonyResponseConstantMap::CODE_TO_CONST[$lNumber->value])) {
            return $lNumber;
        }
        return $this->nodeFactory->createClassConstFetch($this->responseObjectType->getClassName(), SymfonyResponseConstantMap::CODE_TO_CONST[$lNumber->value]);
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function processAssertMethodCall(MethodCall $methodCall)
    {
        $args = $methodCall->getArgs();
        if (!isset($args[1])) {
            return null;
        }
        $secondArg = $args[1];
        if (!$this->isGetStatusMethod($secondArg->value)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, 'Symfony\\Component\\HttpFoundation\\Response', SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function processRedirectMethodCall(MethodCall $methodCall)
    {
        if (!$this->controllerAnalyzer->isController($methodCall->var)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 1, 'Symfony\\Component\\HttpFoundation\\Response', SymfonyResponseConstantMap::CODE_TO_CONST);
    }
    /**
     * @return \PhpParser\Node\Expr\New_|null
     */
    private function processNew(New_ $new)
    {
        if (!$this->isObjectType($new->class, $this->responseObjectType)) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($new, 1, 'Symfony\\Component\\HttpFoundation\\Response', SymfonyResponseConstantMap::CODE_TO_CONST);
    }
}

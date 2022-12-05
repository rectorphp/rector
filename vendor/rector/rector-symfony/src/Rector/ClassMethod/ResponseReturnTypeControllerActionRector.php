<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\ResponseReturnTypeControllerActionRector\ResponseReturnTypeControllerActionRectorTest
 */
final class ResponseReturnTypeControllerActionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttrinationFinder
     */
    private $attrinationFinder;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, AttrinationFinder $attrinationFinder)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->attrinationFinder = $attrinationFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add Response object return type to controller actions', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    #[Route]
    public function detail()
    {
        return $this->render('some_template');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    #[Route]
    public function detail(): Response
    {
        return $this->render('some_template');
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->isPublic()) {
            return null;
        }
        // already filled return type
        if ($node->returnType !== null) {
            return null;
        }
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        if (!$this->attrinationFinder->hasByOne($node, SymfonyAnnotation::ROUTE)) {
            return null;
        }
        if (!$this->hasReturn($node)) {
            return null;
        }
        if ($this->isRedirectResponseReturn($node)) {
            $node->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\RedirectResponse');
            return $node;
        }
        if ($this->isBinaryFileResponseReturn($node)) {
            $node->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\BinaryFileResponse');
            return $node;
        }
        if ($this->isJsonResponseReturn($node)) {
            $node->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\JsonResponse');
            return $node;
        }
        if ($this->isStreamedResponseReturn($node)) {
            $node->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\StreamedResponse');
            return $node;
        }
        if ($this->isResponseReturn($node)) {
            $node->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\Response');
            return $node;
        }
        return $node;
    }
    private function isResponseReturn(ClassMethod $classMethod) : bool
    {
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        foreach ($returns as $return) {
            if (!$return->expr instanceof MethodCall) {
                return \false;
            }
            $methodCall = $return->expr;
            if (!$methodCall->var instanceof Variable || $methodCall->var->name !== 'this') {
                return \false;
            }
            $functionName = $this->getName($methodCall->name);
            if (!\in_array($functionName, ['render', 'forward', 'renderForm'], \true)) {
                return \false;
            }
        }
        return \true;
    }
    private function isRedirectResponseReturn(ClassMethod $classMethod) : bool
    {
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        foreach ($returns as $return) {
            if (!$return->expr instanceof MethodCall) {
                return \false;
            }
            $methodCall = $return->expr;
            if (!$methodCall->var instanceof Variable || $methodCall->var->name !== 'this') {
                return \false;
            }
            $functionName = $this->getName($methodCall->name);
            if (!\in_array($functionName, ['redirectToRoute', 'redirect'], \true)) {
                return \false;
            }
        }
        return \true;
    }
    private function isBinaryFileResponseReturn(ClassMethod $classMethod) : bool
    {
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        foreach ($returns as $return) {
            if (!$return->expr instanceof MethodCall) {
                return \false;
            }
            $methodCall = $return->expr;
            if (!$methodCall->var instanceof Variable || $methodCall->var->name !== 'this') {
                return \false;
            }
            if (!$this->isName($methodCall->name, 'file')) {
                return \false;
            }
        }
        return \true;
    }
    private function isJsonResponseReturn(ClassMethod $classMethod) : bool
    {
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        foreach ($returns as $return) {
            if (!$return->expr instanceof MethodCall) {
                return \false;
            }
            $methodCall = $return->expr;
            if (!$methodCall->var instanceof Variable || $methodCall->var->name !== 'this') {
                return \false;
            }
            if (!$this->isName($methodCall->name, 'json')) {
                return \false;
            }
        }
        return \true;
    }
    private function isStreamedResponseReturn(ClassMethod $classMethod) : bool
    {
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        foreach ($returns as $return) {
            if (!$return->expr instanceof MethodCall) {
                return \false;
            }
            $methodCall = $return->expr;
            if (!$methodCall->var instanceof Variable || $methodCall->var->name !== 'this') {
                return \false;
            }
            if (!$this->isName($methodCall->name, 'stream')) {
                return \false;
            }
        }
        return \true;
    }
    private function hasReturn(ClassMethod $classMethod) : bool
    {
        return $this->betterNodeFinder->hasInstancesOf($classMethod, [Return_::class]);
    }
}

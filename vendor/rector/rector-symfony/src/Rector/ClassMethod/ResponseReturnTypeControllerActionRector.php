<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
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
        if ($this->attrinationFinder->hasByOne($node, SymfonyAnnotation::SENSIO_TEMPLATE) || $this->attrinationFinder->hasByOne($node, SymfonyAnnotation::TWIG_TEMPLATE)) {
            $node->returnType = new NullableType(new Identifier('array'));
            return $node;
        }
        return $this->refactorResponse($node);
    }
    /**
     * @param array<string> $methods
     */
    private function isResponseReturnMethod(ClassMethod $classMethod, array $methods) : bool
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
            if (!\in_array($functionName, $methods, \true)) {
                return \false;
            }
        }
        return \true;
    }
    private function hasReturn(ClassMethod $classMethod) : bool
    {
        return $this->betterNodeFinder->hasInstancesOf($classMethod, [Return_::class]);
    }
    private function refactorResponse(ClassMethod $classMethod) : Node
    {
        if ($this->isResponseReturnMethod($classMethod, ['redirectToRoute', 'redirect'])) {
            $classMethod->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\RedirectResponse');
            return $classMethod;
        }
        if ($this->isResponseReturnMethod($classMethod, ['file'])) {
            $classMethod->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\BinaryFileResponse');
            return $classMethod;
        }
        if ($this->isResponseReturnMethod($classMethod, ['json'])) {
            $classMethod->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\JsonResponse');
            return $classMethod;
        }
        if ($this->isResponseReturnMethod($classMethod, ['stream'])) {
            $classMethod->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\StreamedResponse');
            return $classMethod;
        }
        if ($this->isResponseReturnMethod($classMethod, ['render', 'forward', 'renderForm'])) {
            $classMethod->returnType = new FullyQualified('Symfony\\Component\\HttpFoundation\\Response');
            return $classMethod;
        }
        return $classMethod;
    }
}

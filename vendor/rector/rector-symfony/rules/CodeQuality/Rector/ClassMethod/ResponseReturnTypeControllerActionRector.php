<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Symfony\CodeQuality\Enum\ResponseClass;
use Rector\Symfony\Enum\SensioAttribute;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector\ResponseReturnTypeControllerActionRectorTest
 */
final class ResponseReturnTypeControllerActionRector extends AbstractRector implements MinPhpVersionInterface
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
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, AttrinationFinder $attrinationFinder, BetterNodeFinder $betterNodeFinder, ReturnAnalyzer $returnAnalyzer, StaticTypeMapper $staticTypeMapper, ReturnTypeInferer $returnTypeInferer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->attrinationFinder = $attrinationFinder;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->returnTypeInferer = $returnTypeInferer;
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
        if ($this->attrinationFinder->hasByOne($node, SensioAttribute::TEMPLATE) || $this->attrinationFinder->hasByOne($node, SymfonyAnnotation::TWIG_TEMPLATE)) {
            $returnType = $this->returnTypeInferer->inferFunctionLike($node);
            $types = $returnType instanceof UnionType ? $returnType->getTypes() : [$returnType];
            $objectType = new ObjectType('Symfony\\Component\\HttpFoundation\\Response');
            foreach ($types as $type) {
                if ($type instanceof ObjectType && $objectType->isSuperTypeOf($type)->yes()) {
                    return null;
                }
            }
            if ($returnType->isArray()->yes()) {
                $node->returnType = new Identifier('array');
                return $node;
            }
            return null;
        }
        return $this->refactorResponse($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
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
    private function refactorResponse(ClassMethod $classMethod) : ?ClassMethod
    {
        if ($this->isResponseReturnMethod($classMethod, ['redirectToRoute', 'redirect'])) {
            $classMethod->returnType = new FullyQualified(ResponseClass::REDIRECT);
            return $classMethod;
        }
        if ($this->isResponseReturnMethod($classMethod, ['file'])) {
            $classMethod->returnType = new FullyQualified(ResponseClass::BINARY_FILE);
            return $classMethod;
        }
        if ($this->isResponseReturnMethod($classMethod, ['json'])) {
            $classMethod->returnType = new FullyQualified(ResponseClass::JSON);
            return $classMethod;
        }
        if ($this->isResponseReturnMethod($classMethod, ['stream'])) {
            $classMethod->returnType = new FullyQualified(ResponseClass::STREAMED);
            return $classMethod;
        }
        if ($this->isResponseReturnMethod($classMethod, ['render', 'forward', 'renderForm'])) {
            $classMethod->returnType = new FullyQualified(ResponseClass::BASIC);
            return $classMethod;
        }
        return $this->refatorWithNew($classMethod);
    }
    private function refatorWithNew(ClassMethod $classMethod) : ?ClassMethod
    {
        // early check
        if (!$this->betterNodeFinder->hasInstancesOf($classMethod, [New_::class])) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($classMethod, $returns)) {
            return null;
        }
        $responseReturnType = $this->resolveResponseOnlyReturnType($returns);
        if (!$responseReturnType instanceof Type) {
            return null;
        }
        $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($responseReturnType, TypeKind::RETURN);
        if (!$returnType instanceof FullyQualified) {
            return null;
        }
        $classMethod->returnType = $returnType;
        return $classMethod;
    }
    /**
     * @param Return_[] $returns
     */
    private function resolveResponseOnlyReturnType(array $returns) : ?Type
    {
        $returnedTypes = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                // already validated above
                throw new ShouldNotHappenException();
            }
            $returnedType = $this->getType($return->expr);
            // we only accept response
            if (!$returnedType instanceof ObjectType || !$returnedType->isInstanceOf(ResponseClass::BASIC)->yes()) {
                return null;
            }
            $returnedTypes[] = $returnedType;
        }
        return \count($returnedTypes) > 1 ? new UnionType($returnedTypes) : $returnedTypes[0];
    }
}

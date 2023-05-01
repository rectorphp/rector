<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\CodeQuality\NodeTypeGroup;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeFactory\ThisRenderFactory;
use Rector\Symfony\NodeFinder\EmptyReturnNodeFinder;
use Rector\Symfony\TypeAnalyzer\ArrayUnionResponseTypeAnalyzer;
use Rector\Symfony\TypeDeclaration\ReturnTypeDeclarationUpdater;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202305\Webmozart\Assert\Assert;
/**
 * @see https://github.com/symfony/symfony-docs/pull/12387#discussion_r329551967
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/issues/641
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
 */
final class TemplateAnnotationToThisRenderRector extends AbstractRector
{
    /**
     * @var class-string
     */
    private const RESPONSE_CLASS = 'Symfony\\Component\\HttpFoundation\\Response';
    /**
     * @var string
     */
    private const TEMPLATE_ANNOTATION_CLASS = 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template';
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ArrayUnionResponseTypeAnalyzer
     */
    private $arrayUnionResponseTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\TypeDeclaration\ReturnTypeDeclarationUpdater
     */
    private $returnTypeDeclarationUpdater;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\ThisRenderFactory
     */
    private $thisRenderFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFinder\EmptyReturnNodeFinder
     */
    private $emptyReturnNodeFinder;
    public function __construct(ArrayUnionResponseTypeAnalyzer $arrayUnionResponseTypeAnalyzer, ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater, ThisRenderFactory $thisRenderFactory, PhpDocTagRemover $phpDocTagRemover, EmptyReturnNodeFinder $emptyReturnNodeFinder)
    {
        $this->arrayUnionResponseTypeAnalyzer = $arrayUnionResponseTypeAnalyzer;
        $this->returnTypeDeclarationUpdater = $returnTypeDeclarationUpdater;
        $this->thisRenderFactory = $thisRenderFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->emptyReturnNodeFinder = $emptyReturnNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @Template()
 */
public function indexAction()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
public function indexAction()
{
    return $this->render('index.html.twig');
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Class_::class];
    }
    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Class_) {
            return $this->addAbstractControllerParentClassIfMissing($node);
        }
        return $this->replaceTemplateAnnotation($node);
    }
    private function addAbstractControllerParentClassIfMissing(Class_ $class) : ?Class_
    {
        if ($class->extends instanceof Name) {
            return null;
        }
        if (!$this->hasClassMethodWithTemplateAnnotation($class)) {
            return null;
        }
        $class->extends = new FullyQualified('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController');
        return $class;
    }
    private function replaceTemplateAnnotation(ClassMethod $classMethod) : ?Node
    {
        if (!$classMethod->isPublic()) {
            return null;
        }
        $doctrineAnnotationTagValueNode = $this->getDoctrineAnnotationTagValueNode($classMethod, self::TEMPLATE_ANNOTATION_CLASS);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $this->refactorClassMethod($classMethod, $doctrineAnnotationTagValueNode);
        return $classMethod;
    }
    private function hasClassMethodWithTemplateAnnotation(Class_ $class) : bool
    {
        foreach ($class->getMethods() as $classMethod) {
            $templateDoctrineAnnotationTagValueNode = $this->getDoctrineAnnotationTagValueNode($classMethod, self::TEMPLATE_ANNOTATION_CLASS);
            if ($templateDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                return \true;
            }
        }
        return \false;
    }
    private function refactorClassMethod(ClassMethod $classMethod, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : void
    {
        $hasThisRenderOrReturnsResponse = $this->hasLastReturnResponse($classMethod);
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use($templateDoctrineAnnotationTagValueNode, $hasThisRenderOrReturnsResponse, $classMethod) {
            // keep as similar type
            if ($node instanceof Closure || $node instanceof Function_) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Stmt) {
                return null;
            }
            foreach (NodeTypeGroup::STMTS_AWARE as $stmtsAwareType) {
                if (!$node instanceof $stmtsAwareType) {
                    continue;
                }
                $this->refactorStmtsAwareNode($node, $templateDoctrineAnnotationTagValueNode, $hasThisRenderOrReturnsResponse, $classMethod);
                return null;
            }
            return null;
        });
        if (!$this->emptyReturnNodeFinder->hasNoOrEmptyReturns($classMethod)) {
            return;
        }
        $thisRenderMethodCall = $this->thisRenderFactory->create(null, $templateDoctrineAnnotationTagValueNode, $classMethod);
        $this->refactorNoReturn($classMethod, $thisRenderMethodCall, $templateDoctrineAnnotationTagValueNode);
    }
    private function hasLastReturnResponse(ClassMethod $classMethod) : bool
    {
        $node = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, Return_::class);
        if (!$node instanceof Return_) {
            return \false;
        }
        if (!$node->expr instanceof Expr) {
            return \false;
        }
        $responseObjectType = new ObjectType(self::RESPONSE_CLASS);
        $returnType = $this->getType($node->expr);
        return $responseObjectType->isSuperTypeOf($returnType)->yes();
    }
    private function refactorReturn(Return_ $return, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode, bool $hasThisRenderOrReturnsResponse, ClassMethod $classMethod) : void
    {
        // nothing we can do
        if (!$return->expr instanceof Expr) {
            return;
        }
        // create "$this->render('template.file.twig.html', ['key' => 'value']);" method call
        $thisRenderMethodCall = $this->thisRenderFactory->create($return, $templateDoctrineAnnotationTagValueNode, $classMethod);
        $this->refactorReturnWithValue($return, $hasThisRenderOrReturnsResponse, $thisRenderMethodCall, $classMethod, $templateDoctrineAnnotationTagValueNode);
    }
    private function getDoctrineAnnotationTagValueNode(ClassMethod $classMethod, string $class) : ?DoctrineAnnotationTagValueNode
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        return $phpDocInfo->getByAnnotationClass($class);
    }
    private function refactorNoReturn(ClassMethod $classMethod, MethodCall $thisRenderMethodCall, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $classMethod->stmts[] = new Return_($thisRenderMethodCall);
        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, self::RESPONSE_CLASS);
        $this->removeDoctrineAnnotationTagValueNode($classMethod, $doctrineAnnotationTagValueNode);
    }
    private function refactorReturnWithValue(Return_ $return, bool $hasThisRenderOrReturnsResponse, MethodCall $thisRenderMethodCall, ClassMethod $classMethod, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        /** @var Expr $lastReturnExpr */
        $lastReturnExpr = $return->expr;
        $returnStaticType = $this->getType($lastReturnExpr);
        if (!$return->expr instanceof MethodCall) {
            if (!$hasThisRenderOrReturnsResponse || $returnStaticType instanceof ConstantArrayType) {
                $return->expr = $thisRenderMethodCall;
            }
        } elseif ($returnStaticType instanceof ArrayType) {
            $return->expr = $thisRenderMethodCall;
        } elseif ($returnStaticType instanceof MixedType) {
            // nothing we can do
            return;
        }
        $isArrayOrResponseType = $this->arrayUnionResponseTypeAnalyzer->isArrayUnionResponseType($returnStaticType, self::RESPONSE_CLASS);
        if ($isArrayOrResponseType) {
            $this->processIsArrayOrResponseType($classMethod, $return, $lastReturnExpr, $thisRenderMethodCall);
        }
        // already response
        $this->removeDoctrineAnnotationTagValueNode($classMethod, $doctrineAnnotationTagValueNode);
        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, self::RESPONSE_CLASS);
    }
    private function processIsArrayOrResponseType(ClassMethod $classMethod, Return_ $return, Expr $returnExpr, MethodCall $thisRenderMethodCall) : void
    {
        $this->removeNode($return);
        // create instance of Response → return response, or return $this->render
        $responseVariable = new Variable('responseOrData');
        $assign = new Assign($responseVariable, $returnExpr);
        $assignExpression = new Expression($assign);
        $if = new If_(new Instanceof_($responseVariable, new FullyQualified(self::RESPONSE_CLASS)));
        $if->stmts[] = new Return_($responseVariable);
        $thisRenderMethodCall->args[1] = new Arg($responseVariable);
        $returnThisRender = new Return_($thisRenderMethodCall);
        $classMethodStmts = (array) $classMethod->stmts;
        $classMethod->stmts = \array_merge($classMethodStmts, [$assignExpression, $if, $returnThisRender]);
    }
    private function removeDoctrineAnnotationTagValueNode(ClassMethod $classMethod, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);
    }
    private function refactorStmtsAwareNode(Stmt $stmtsAwareStmt, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode, bool $hasThisRenderOrReturnsResponse, ClassMethod $classMethod) : void
    {
        Assert::propertyExists($stmtsAwareStmt, 'stmts');
        foreach ((array) $stmtsAwareStmt->stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            // just created node, skip it
            if ($stmt->getAttributes() === []) {
                return;
            }
            $this->refactorReturn($stmt, $templateDoctrineAnnotationTagValueNode, $hasThisRenderOrReturnsResponse, $classMethod);
        }
    }
}

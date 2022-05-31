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
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see https://github.com/symfony/symfony-docs/pull/12387#discussion_r329551967
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
 * @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/issues/641
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
 */
final class TemplateAnnotationToThisRenderRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Symfony\TypeAnalyzer\ArrayUnionResponseTypeAnalyzer $arrayUnionResponseTypeAnalyzer, \Rector\Symfony\TypeDeclaration\ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater, \Rector\Symfony\NodeFactory\ThisRenderFactory $thisRenderFactory, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\Symfony\NodeFinder\EmptyReturnNodeFinder $emptyReturnNodeFinder)
    {
        $this->arrayUnionResponseTypeAnalyzer = $arrayUnionResponseTypeAnalyzer;
        $this->returnTypeDeclarationUpdater = $returnTypeDeclarationUpdater;
        $this->thisRenderFactory = $thisRenderFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->emptyReturnNodeFinder = $emptyReturnNodeFinder;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            return $this->addAbstractControllerParentClassIfMissing($node);
        }
        return $this->replaceTemplateAnnotation($node);
    }
    private function addAbstractControllerParentClassIfMissing(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node\Stmt\Class_
    {
        if ($class->extends !== null) {
            return null;
        }
        if (!$this->hasClassMethodWithTemplateAnnotation($class)) {
            return null;
        }
        $class->extends = new \PhpParser\Node\Name\FullyQualified('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController');
        return $class;
    }
    private function replaceTemplateAnnotation(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node
    {
        if (!$classMethod->isPublic()) {
            return null;
        }
        $doctrineAnnotationTagValueNode = $this->getDoctrineAnnotationTagValueNode($classMethod, self::TEMPLATE_ANNOTATION_CLASS);
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        $this->refactorClassMethod($classMethod, $doctrineAnnotationTagValueNode);
        return $classMethod;
    }
    private function hasClassMethodWithTemplateAnnotation(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        foreach ($class->getMethods() as $classMethod) {
            $templateDoctrineAnnotationTagValueNode = $this->getDoctrineAnnotationTagValueNode($classMethod, self::TEMPLATE_ANNOTATION_CLASS);
            if ($templateDoctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                return \true;
            }
        }
        return \false;
    }
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : void
    {
        $hasThisRenderOrReturnsResponse = $this->hasLastReturnResponse($classMethod);
        $this->traverseNodesWithCallable($classMethod, function (\PhpParser\Node $node) use($templateDoctrineAnnotationTagValueNode, $hasThisRenderOrReturnsResponse, $classMethod) {
            // keep as similar type
            if ($node instanceof \PhpParser\Node\Expr\Closure || $node instanceof \PhpParser\Node\Stmt\Function_) {
                return \PhpParser\NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof \PhpParser\Node\Stmt) {
                return null;
            }
            foreach (\Rector\CodeQuality\NodeTypeGroup::STMTS_AWARE as $stmtsAwareType) {
                if (!\is_a($node, $stmtsAwareType, \true)) {
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
    private function hasLastReturnResponse(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Stmt\Return_::class);
        if (!$lastReturn instanceof \PhpParser\Node\Stmt\Return_) {
            return \false;
        }
        if ($lastReturn->expr === null) {
            return \false;
        }
        $responseObjectType = new \PHPStan\Type\ObjectType(self::RESPONSE_CLASS);
        $returnType = $this->getType($lastReturn->expr);
        return $responseObjectType->isSuperTypeOf($returnType)->yes();
    }
    private function refactorReturn(\PhpParser\Node\Stmt\Return_ $return, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode, bool $hasThisRenderOrReturnsResponse, \PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        // nothing we can do
        if ($return->expr === null) {
            return;
        }
        // create "$this->render('template.file.twig.html', ['key' => 'value']);" method call
        $thisRenderMethodCall = $this->thisRenderFactory->create($return, $templateDoctrineAnnotationTagValueNode, $classMethod);
        $this->refactorReturnWithValue($return, $hasThisRenderOrReturnsResponse, $thisRenderMethodCall, $classMethod, $templateDoctrineAnnotationTagValueNode);
    }
    private function getDoctrineAnnotationTagValueNode(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $class) : ?\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        return $phpDocInfo->getByAnnotationClass($class);
    }
    private function refactorNoReturn(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\MethodCall $thisRenderMethodCall, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $classMethod->stmts[] = new \PhpParser\Node\Stmt\Return_($thisRenderMethodCall);
        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, self::RESPONSE_CLASS);
        $this->removeDoctrineAnnotationTagValueNode($classMethod, $doctrineAnnotationTagValueNode);
    }
    private function refactorReturnWithValue(\PhpParser\Node\Stmt\Return_ $return, bool $hasThisRenderOrReturnsResponse, \PhpParser\Node\Expr\MethodCall $thisRenderMethodCall, \PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        /** @var Expr $lastReturnExpr */
        $lastReturnExpr = $return->expr;
        $returnStaticType = $this->getType($lastReturnExpr);
        if (!$return->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            if (!$hasThisRenderOrReturnsResponse || $returnStaticType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
                $return->expr = $thisRenderMethodCall;
            }
        } elseif ($returnStaticType instanceof \PHPStan\Type\ArrayType) {
            $return->expr = $thisRenderMethodCall;
        } elseif ($returnStaticType instanceof \PHPStan\Type\MixedType) {
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
    private function processIsArrayOrResponseType(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Stmt\Return_ $return, \PhpParser\Node\Expr $returnExpr, \PhpParser\Node\Expr\MethodCall $thisRenderMethodCall) : void
    {
        $this->removeNode($return);
        // create instance of Response → return response, or return $this->render
        $responseVariable = new \PhpParser\Node\Expr\Variable('responseOrData');
        $assign = new \PhpParser\Node\Expr\Assign($responseVariable, $returnExpr);
        $assignExpression = new \PhpParser\Node\Stmt\Expression($assign);
        $if = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\Instanceof_($responseVariable, new \PhpParser\Node\Name\FullyQualified(self::RESPONSE_CLASS)));
        $if->stmts[] = new \PhpParser\Node\Stmt\Return_($responseVariable);
        $thisRenderMethodCall->args[1] = new \PhpParser\Node\Arg($responseVariable);
        $returnThisRender = new \PhpParser\Node\Stmt\Return_($thisRenderMethodCall);
        $classMethodStmts = (array) $classMethod->stmts;
        $classMethod->stmts = \array_merge($classMethodStmts, [$assignExpression, $if, $returnThisRender]);
    }
    private function removeDoctrineAnnotationTagValueNode(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);
    }
    private function refactorStmtsAwareNode(\PhpParser\Node\Stmt $stmtsAwareStmt, \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode, bool $hasThisRenderOrReturnsResponse, \PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        \RectorPrefix20220531\Webmozart\Assert\Assert::propertyExists($stmtsAwareStmt, 'stmts');
        foreach ((array) $stmtsAwareStmt->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Return_) {
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

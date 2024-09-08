<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Annotation\AnnotationAnalyzer;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\NodeFactory\ThisRenderFactory;
use Rector\Symfony\NodeFinder\EmptyReturnNodeFinder;
use Rector\Symfony\TypeAnalyzer\ArrayUnionResponseTypeAnalyzer;
use Rector\Symfony\TypeDeclaration\ReturnTypeDeclarationUpdater;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony-docs/pull/12387#discussion_r329551967
 * @changelog https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
 */
final class TemplateAnnotationToThisRenderRector extends AbstractRector
{
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
    /**
     * @readonly
     * @var \Rector\Symfony\Annotation\AnnotationAnalyzer
     */
    private $annotationAnalyzer;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(ArrayUnionResponseTypeAnalyzer $arrayUnionResponseTypeAnalyzer, ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater, ThisRenderFactory $thisRenderFactory, PhpDocTagRemover $phpDocTagRemover, EmptyReturnNodeFinder $emptyReturnNodeFinder, AnnotationAnalyzer $annotationAnalyzer, DocBlockUpdater $docBlockUpdater, BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->arrayUnionResponseTypeAnalyzer = $arrayUnionResponseTypeAnalyzer;
        $this->returnTypeDeclarationUpdater = $returnTypeDeclarationUpdater;
        $this->thisRenderFactory = $thisRenderFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->emptyReturnNodeFinder = $emptyReturnNodeFinder;
        $this->annotationAnalyzer = $annotationAnalyzer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SomeController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SomeController
{
    public function indexAction()
    {
        return $this->render('index.html.twig');
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->annotationAnalyzer->hasClassMethodWithTemplateAnnotation($node)) {
            return null;
        }
        $this->decorateAbstractControllerParentClass($node);
        $hasChanged = \false;
        $classDoctrineAnnotationTagValueNode = $this->annotationAnalyzer->getDoctrineAnnotationTagValueNode($node, SymfonyAnnotation::TEMPLATE);
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            $hasClassMethodChanged = $this->replaceTemplateAnnotation($classMethod, $classDoctrineAnnotationTagValueNode);
            if ($hasClassMethodChanged) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        // cleanup Class_ @Template annotaion
        if ($classDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            $this->removeDoctrineAnnotationTagValueNode($node, $classDoctrineAnnotationTagValueNode);
        }
        return $node;
    }
    private function decorateAbstractControllerParentClass(Class_ $class) : void
    {
        if ($class->extends instanceof Name) {
            return;
        }
        // this will make $this->render() method available
        $class->extends = new FullyQualified('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController');
    }
    private function replaceTemplateAnnotation(ClassMethod $classMethod, ?DoctrineAnnotationTagValueNode $classDoctrineAnnotationTagValueNode) : bool
    {
        if (!$classMethod->isPublic()) {
            return \false;
        }
        $doctrineAnnotationTagValueNode = $this->annotationAnalyzer->getDoctrineAnnotationTagValueNode($classMethod, SymfonyAnnotation::TEMPLATE);
        if ($doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return $this->refactorClassMethod($classMethod, $doctrineAnnotationTagValueNode);
        }
        // global @Template access
        if ($classDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return $this->refactorClassMethod($classMethod, $classDoctrineAnnotationTagValueNode);
        }
        return \false;
    }
    private function refactorClassMethod(ClassMethod $classMethod, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode) : bool
    {
        $hasThisRenderOrReturnsResponse = $this->hasLastReturnResponse($classMethod);
        $hasChanged = \false;
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use($templateDoctrineAnnotationTagValueNode, $hasThisRenderOrReturnsResponse, $classMethod, &$hasChanged) : ?int {
            // keep as similar type
            if ($node instanceof Closure || $node instanceof Function_) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof StmtsAwareInterface) {
                return null;
            }
            $this->refactorStmtsAwareNode($node, $templateDoctrineAnnotationTagValueNode, $hasThisRenderOrReturnsResponse, $classMethod);
            $hasChanged = \true;
            return null;
        });
        if (!$this->emptyReturnNodeFinder->hasNoOrEmptyReturns($classMethod)) {
            return $hasChanged;
        }
        $thisRenderMethodCall = $this->thisRenderFactory->create(null, $templateDoctrineAnnotationTagValueNode, $classMethod);
        $this->refactorNoReturn($classMethod, $thisRenderMethodCall, $templateDoctrineAnnotationTagValueNode);
        return \true;
    }
    private function hasLastReturnResponse(ClassMethod $classMethod) : bool
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Return_::class);
        if ($returns === []) {
            return \false;
        }
        $lastReturn = \array_pop($returns);
        if (!$lastReturn->expr instanceof Expr) {
            return \false;
        }
        $responseObjectType = new ObjectType(SymfonyClass::RESPONSE);
        $returnType = $this->getType($lastReturn->expr);
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
    private function refactorNoReturn(ClassMethod $classMethod, MethodCall $thisRenderMethodCall, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $classMethod->stmts[] = new Return_($thisRenderMethodCall);
        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, SymfonyClass::RESPONSE);
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
        $isArrayOrResponseType = $this->arrayUnionResponseTypeAnalyzer->isArrayUnionResponseType($returnStaticType, SymfonyClass::RESPONSE);
        // skip as the original class method has to change first
        if ($isArrayOrResponseType) {
            return;
        }
        // already response
        $this->removeDoctrineAnnotationTagValueNode($classMethod, $doctrineAnnotationTagValueNode);
        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, SymfonyClass::RESPONSE);
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod $node
     */
    private function removeDoctrineAnnotationTagValueNode($node, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
    }
    private function refactorStmtsAwareNode(StmtsAwareInterface $stmtsAware, DoctrineAnnotationTagValueNode $templateDoctrineAnnotationTagValueNode, bool $hasThisRenderOrReturnsResponse, ClassMethod $classMethod) : void
    {
        if ($stmtsAware->stmts === null) {
            return;
        }
        foreach ($stmtsAware->stmts as $stmt) {
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

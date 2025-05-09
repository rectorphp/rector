<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Doctrine\NodeAnalyzer\AttrinationFinder;
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
 * @see https://github.com/symfony/symfony-docs/pull/12387#discussion_r329551967
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
 */
final class TemplateAnnotationToThisRenderRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArrayUnionResponseTypeAnalyzer $arrayUnionResponseTypeAnalyzer;
    /**
     * @readonly
     */
    private ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater;
    /**
     * @readonly
     */
    private ThisRenderFactory $thisRenderFactory;
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private EmptyReturnNodeFinder $emptyReturnNodeFinder;
    /**
     * @readonly
     */
    private AnnotationAnalyzer $annotationAnalyzer;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private AttrinationFinder $attrinationFinder;
    public function __construct(ArrayUnionResponseTypeAnalyzer $arrayUnionResponseTypeAnalyzer, ReturnTypeDeclarationUpdater $returnTypeDeclarationUpdater, ThisRenderFactory $thisRenderFactory, PhpDocTagRemover $phpDocTagRemover, EmptyReturnNodeFinder $emptyReturnNodeFinder, AnnotationAnalyzer $annotationAnalyzer, DocBlockUpdater $docBlockUpdater, BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory, AttrinationFinder $attrinationFinder)
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
        $this->attrinationFinder = $attrinationFinder;
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
        $hasChanged = \false;
        $classTemplateTagValueNodeOrAttribute = $this->attrinationFinder->getByOne($node, SymfonyAnnotation::TEMPLATE);
        foreach ($node->getMethods() as $classMethod) {
            $hasClassMethodChanged = $this->replaceTemplateAnnotation($classMethod, $classTemplateTagValueNodeOrAttribute);
            if ($hasClassMethodChanged) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        $this->decorateAbstractControllerParentClass($node);
        // cleanup Class_ @Template annotation
        if ($classTemplateTagValueNodeOrAttribute instanceof DoctrineAnnotationTagValueNode) {
            $this->removeDoctrineAnnotationTagValueNode($node, $classTemplateTagValueNodeOrAttribute);
        }
        return $node;
    }
    private function decorateAbstractControllerParentClass(Class_ $class) : void
    {
        if ($class->extends instanceof Name) {
            return;
        }
        // this will make $this->render() method available
        $class->extends = new FullyQualified(SymfonyClass::ABSTRACT_CONTROLLER);
    }
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute|null $classTagValueNodeOrAttribute
     */
    private function replaceTemplateAnnotation(ClassMethod $classMethod, $classTagValueNodeOrAttribute) : bool
    {
        if (!$classMethod->isPublic()) {
            return \false;
        }
        $methodTemplateTagValueNodeOrAttribute = $this->attrinationFinder->getByOne($classMethod, SymfonyAnnotation::TEMPLATE);
        if ($methodTemplateTagValueNodeOrAttribute !== null) {
            return $this->refactorClassMethod($classMethod, $methodTemplateTagValueNodeOrAttribute);
        }
        // fallback to global @Template/#[Template] access
        if ($classTagValueNodeOrAttribute instanceof DoctrineAnnotationTagValueNode || $classTagValueNodeOrAttribute instanceof Attribute) {
            return $this->refactorClassMethod($classMethod, $classTagValueNodeOrAttribute);
        }
        return \false;
    }
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $templateTagValueNodeOrAttribute
     */
    private function refactorClassMethod(ClassMethod $classMethod, $templateTagValueNodeOrAttribute) : bool
    {
        $hasThisRenderOrReturnsResponse = $this->hasLastReturnResponse($classMethod);
        $hasChanged = \false;
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use($templateTagValueNodeOrAttribute, $hasThisRenderOrReturnsResponse, $classMethod, &$hasChanged) : ?int {
            // keep as similar type
            if ($node instanceof Closure || $node instanceof Function_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof StmtsAwareInterface) {
                return null;
            }
            $hasChangedNode = $this->refactorStmtsAwareNode($node, $templateTagValueNodeOrAttribute, $hasThisRenderOrReturnsResponse, $classMethod);
            if ($hasChangedNode) {
                $hasChanged = \true;
            }
            return null;
        });
        // remove return array shape details
        $this->removeReturnArrayShapeDocblock($classMethod);
        if (!$this->emptyReturnNodeFinder->hasNoOrEmptyReturns($classMethod)) {
            return $hasChanged;
        }
        $thisRenderMethodCall = $this->thisRenderFactory->create(null, $templateTagValueNodeOrAttribute, $classMethod);
        $this->refactorNoReturn($classMethod, $thisRenderMethodCall, $templateTagValueNodeOrAttribute);
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
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $templateTagValueNodeOrAttribute
     */
    private function refactorReturn(Return_ $return, $templateTagValueNodeOrAttribute, bool $hasThisRenderOrReturnsResponse, ClassMethod $classMethod) : bool
    {
        // nothing we can do
        if (!$return->expr instanceof Expr) {
            return \false;
        }
        // create "$this->render('template.file.twig.html', ['key' => 'value']);" method call
        $thisRenderMethodCall = $this->thisRenderFactory->create($return, $templateTagValueNodeOrAttribute, $classMethod);
        return $this->refactorReturnWithValue($return, $hasThisRenderOrReturnsResponse, $thisRenderMethodCall, $classMethod, $templateTagValueNodeOrAttribute);
    }
    private function refactorNoReturn(ClassMethod $classMethod, MethodCall $thisRenderMethodCall, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $classMethod->stmts[] = new Return_($thisRenderMethodCall);
        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, SymfonyClass::RESPONSE);
        $this->removeDoctrineAnnotationTagValueNode($classMethod, $doctrineAnnotationTagValueNode);
    }
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $doctrineTagValueNodeOrAttribute
     */
    private function refactorReturnWithValue(Return_ $return, bool $hasThisRenderOrReturnsResponse, MethodCall $thisRenderMethodCall, ClassMethod $classMethod, $doctrineTagValueNodeOrAttribute) : bool
    {
        /** @var Expr $lastReturnExpr */
        $lastReturnExpr = $return->expr;
        $returnStaticType = $this->getType($lastReturnExpr);
        $responseObjectType = new ObjectType(SymfonyClass::RESPONSE);
        // change contents only if the value is not Response yet
        if (!$responseObjectType->isSuperTypeOf($returnStaticType)->yes()) {
            if (!$return->expr instanceof MethodCall) {
                if (!$hasThisRenderOrReturnsResponse || $returnStaticType instanceof ConstantArrayType) {
                    $return->expr = $thisRenderMethodCall;
                }
            } elseif ($returnStaticType instanceof ArrayType) {
                $return->expr = $thisRenderMethodCall;
            } elseif ($returnStaticType instanceof MixedType) {
                // nothing we can do
                return \false;
            }
            $isArrayOrResponseType = $this->arrayUnionResponseTypeAnalyzer->isArrayUnionResponseType($returnStaticType, SymfonyClass::RESPONSE);
            // skip as the original class method has to change first
            if ($isArrayOrResponseType) {
                return \false;
            }
        }
        // already response
        $this->removeDoctrineAnnotationTagValueNode($classMethod, $doctrineTagValueNodeOrAttribute);
        $this->returnTypeDeclarationUpdater->updateClassMethod($classMethod, SymfonyClass::RESPONSE);
        return \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod $node
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $doctrineTagValueNodeOrAttribute
     */
    private function removeDoctrineAnnotationTagValueNode($node, $doctrineTagValueNodeOrAttribute) : void
    {
        if ($doctrineTagValueNodeOrAttribute instanceof DoctrineAnnotationTagValueNode) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineTagValueNodeOrAttribute);
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
            return;
        }
        foreach ($node->attrGroups as $attrGroupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $attributeKey => $attribute) {
                if ($attribute === $doctrineTagValueNodeOrAttribute) {
                    unset($attrGroup->attrs[$attributeKey]);
                }
            }
            // no attributes left? remove the whole dgroup
            if ($attrGroup->attrs === []) {
                unset($node->attrGroups[$attrGroupKey]);
            }
        }
    }
    /**
     * @param \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode|\PhpParser\Node\Attribute $templateTagValueNodeOrAttribute
     */
    private function refactorStmtsAwareNode(StmtsAwareInterface $stmtsAware, $templateTagValueNodeOrAttribute, bool $hasThisRenderOrReturnsResponse, ClassMethod $classMethod) : bool
    {
        if ($stmtsAware->stmts === null) {
            return \false;
        }
        $hasChanged = \false;
        foreach ($stmtsAware->stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            // just created class, skip it
            if ($stmt->getAttributes() === []) {
                return \false;
            }
            $hasChangedReturn = $this->refactorReturn($stmt, $templateTagValueNodeOrAttribute, $hasThisRenderOrReturnsResponse, $classMethod);
            if ($hasChangedReturn) {
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
    private function removeReturnArrayShapeDocblock(ClassMethod $classMethod) : void
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $returnTagValueNode = $classMethodPhpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return;
        }
        if (!$returnTagValueNode->type instanceof ArrayShapeNode) {
            return;
        }
        if ($classMethodPhpDocInfo->removeByName('@return')) {
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        }
    }
}

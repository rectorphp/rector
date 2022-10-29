<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#method-annotation
 * @see https://stackoverflow.com/questions/51171934/how-to-fix-symfony-3-4-route-and-method-deprecation
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector\MergeMethodAnnotationToRouteAnnotationRectorTest
 */
final class MergeMethodAnnotationToRouteAnnotationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;
    public function __construct(PhpDocTagRemover $phpDocTagRemover, PhpDocInfoPrinter $phpDocInfoPrinter)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Merge removed @Method annotation to @Route one', [new CodeSample(<<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/show/{id}")
     * @Method({"GET", "HEAD"})
     */
    public function show($id)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/show/{id}", methods={"GET","HEAD"})
     */
    public function show($id)
    {
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
        $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        if (!$node->isPublic()) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $sensioDoctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(SymfonyAnnotation::SENSIO_METHOD);
        if (!$sensioDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $symfonyDoctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(SymfonyAnnotation::ROUTE);
        if (!$symfonyDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $sensioMethods = $this->resolveMethods($sensioDoctrineAnnotationTagValueNode);
        if ($sensioMethods === null) {
            return null;
        }
        if (\is_string($sensioMethods)) {
            $sensioMethods = new CurlyListNode([new ArrayItemNode($sensioMethods, null, String_::KIND_DOUBLE_QUOTED)]);
        }
        $symfonyMethodsArrayItemNode = $symfonyDoctrineAnnotationTagValueNode->getValue('methods');
        // value is already filled, do not enter anything
        if ($symfonyMethodsArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        $symfonyDoctrineAnnotationTagValueNode->values[] = new ArrayItemNode($sensioMethods, 'methods');
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $sensioDoctrineAnnotationTagValueNode);
        $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
        return $node;
    }
    /**
     * @return string|string[]|null|CurlyListNode
     */
    private function resolveMethods(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode)
    {
        $methodsParameter = $doctrineAnnotationTagValueNode->getValue('methods');
        if ($methodsParameter instanceof ArrayItemNode && $methodsParameter->value instanceof CurlyListNode) {
            return $methodsParameter->value;
        }
        $arrayItemNode = $doctrineAnnotationTagValueNode->getSilentValue();
        if ($arrayItemNode instanceof ArrayItemNode) {
            return $arrayItemNode->value;
        }
        return null;
    }
}

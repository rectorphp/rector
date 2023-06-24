<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony34\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\Enum\SensioAttribute;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#method-annotation
 * @changelog https://stackoverflow.com/questions/51171934/how-to-fix-symfony-3-4-route-and-method-deprecation
 *
 * @see \Rector\Symfony\Tests\Symfony34\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector\MergeMethodAnnotationToRouteAnnotationRectorTest
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
        return [ClassLike::class];
    }
    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $sensioDoctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(SensioAttribute::METHOD);
            if (!$sensioDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $symfonyDoctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(SymfonyAnnotation::ROUTE);
            if (!$symfonyDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $sensioMethods = $this->resolveMethods($sensioDoctrineAnnotationTagValueNode);
            if ($sensioMethods === null) {
                continue;
            }
            if (\is_string($sensioMethods) || $sensioMethods instanceof StringNode) {
                $sensioMethods = new CurlyListNode([new ArrayItemNode($sensioMethods)]);
            }
            $symfonyMethodsArrayItemNode = $symfonyDoctrineAnnotationTagValueNode->getValue('methods');
            // value is already filled, do not enter anything
            if ($symfonyMethodsArrayItemNode instanceof ArrayItemNode) {
                continue;
            }
            $symfonyDoctrineAnnotationTagValueNode->values[] = new ArrayItemNode($sensioMethods, 'methods');
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $sensioDoctrineAnnotationTagValueNode);
            $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @return string|string[]|null|CurlyListNode|StringNode
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

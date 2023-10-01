<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony34\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\Symfony\PhpDocNode\SymfonyRouteTagValueNodeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://medium.com/@nebkam/symfony-deprecated-route-and-method-annotations-4d5e1d34556a
 * @changelog https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html#method-annotation
 *
 * @see \Rector\Symfony\Tests\Symfony34\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\ReplaceSensioRouteAnnotationWithSymfonyRectorTest
 */
final class ReplaceSensioRouteAnnotationWithSymfonyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\PhpDocNode\SymfonyRouteTagValueNodeFactory
     */
    private $symfonyRouteTagValueNodeFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var string
     */
    private const SENSIO_ROUTE_NAME = 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Route';
    public function __construct(SymfonyRouteTagValueNodeFactory $symfonyRouteTagValueNodeFactory, PhpDocTagRemover $phpDocTagRemover, RenamedClassesDataCollector $renamedClassesDataCollector, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->symfonyRouteTagValueNodeFactory = $symfonyRouteTagValueNodeFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace Sensio @Route annotation with Symfony one', [new CodeSample(<<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

final class SomeClass
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

final class SomeClass
{
    /**
     * @Route()
     */
    public function run()
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
        return [ClassMethod::class, Class_::class];
    }
    /**
     * @param ClassMethod|Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        // early return in case of non public method
        if ($node instanceof ClassMethod && !$node->isPublic()) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $sensioDoctrineAnnotationTagValueNodes = $phpDocInfo->findByAnnotationClass(self::SENSIO_ROUTE_NAME);
        // nothing to find
        if ($sensioDoctrineAnnotationTagValueNodes === []) {
            return null;
        }
        foreach ($sensioDoctrineAnnotationTagValueNodes as $sensioDoctrineAnnotationTagValueNode) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $sensioDoctrineAnnotationTagValueNode);
            // unset service, that is deprecated
            $sensioDoctrineAnnotationTagValueNode->removeValue('service');
            $values = $sensioDoctrineAnnotationTagValueNode->getValues();
            $symfonyRouteTagValueNode = $this->symfonyRouteTagValueNodeFactory->createFromItems($values);
            // avoid adding this one
            if ($node instanceof Class_ && $this->isEmptySensioRoute($values)) {
                continue;
            }
            $phpDocInfo->addTagValueNode($symfonyRouteTagValueNode);
        }
        $this->renamedClassesDataCollector->addOldToNewClasses([self::SENSIO_ROUTE_NAME => SymfonyAnnotation::ROUTE]);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    /**
     * @param mixed[] $values
     */
    private function isEmptySensioRoute(array $values) : bool
    {
        if ($values === []) {
            return \true;
        }
        if (\count($values) !== 1) {
            return \false;
        }
        $singleValue = $values[0];
        if (!$singleValue instanceof ArrayItemNode) {
            return \false;
        }
        if ($singleValue->key !== null) {
            return \false;
        }
        $stringNode = $singleValue->value;
        if (!$stringNode instanceof StringNode) {
            return \false;
        }
        return $singleValue->value->value === '/';
    }
}

<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Rector\NodeFactory\DoctrineAnnotationFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202304\Webmozart\Assert\Assert;
/**
 * @changelog https://php.watch/articles/php-attributes#syntax
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\DowngradeAttributeToAnnotationRectorTest
 */
final class DowngradeAttributeToAnnotationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private const SKIPPED_ATTRIBUTES = ['Attribute', 'ReturnTypeWillChange'];
    /**
     * @var DowngradeAttributeToAnnotation[]
     */
    private $attributesToAnnotations = [];
    /**
     * @var bool
     */
    private $isDowngraded = \false;
    /**
     * @readonly
     * @var \Rector\NodeFactory\DoctrineAnnotationFactory
     */
    private $doctrineAnnotationFactory;
    public function __construct(DoctrineAnnotationFactory $doctrineAnnotationFactory)
    {
        $this->doctrineAnnotationFactory = $doctrineAnnotationFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor PHP attribute markers to annotations notation', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    #[Route(path: '/path', name: 'action')]
    public function action()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    /**
     * @Route("/path", name="action")
     */
    public function action()
    {
    }
}
CODE_SAMPLE
, [new DowngradeAttributeToAnnotation('Symfony\\Component\\Routing\\Annotation\\Route')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, ClassMethod::class, Property::class, Interface_::class, Param::class, Function_::class];
    }
    /**
     * @param Class_|ClassMethod|Property|Interface_|Param|Function_  $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->isDowngraded = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $key => $attribute) {
                if ($this->shouldSkipAttribute($attribute)) {
                    continue;
                }
                $attributeToAnnotation = $this->matchAttributeToAnnotation($attribute, $this->attributesToAnnotations);
                if (!$attributeToAnnotation instanceof DowngradeAttributeToAnnotation) {
                    // clear the attribute to avoid inlining to a comment that will ignore the rest of the line
                    unset($attrGroup->attrs[$key]);
                    continue;
                }
                unset($attrGroup->attrs[$key]);
                if (\strpos($attributeToAnnotation->getTag(), '\\') === \false) {
                    $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@' . $attributeToAnnotation->getTag(), new GenericTagValueNode('')));
                } else {
                    $doctrineAnnotation = $this->doctrineAnnotationFactory->createFromAttribute($attribute, $attributeToAnnotation->getTag());
                    $phpDocInfo->addTagValueNode($doctrineAnnotation);
                }
                $this->isDowngraded = \true;
            }
        }
        // cleanup empty attr groups
        $this->cleanupEmptyAttrGroups($node);
        if (!$this->isDowngraded) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, DowngradeAttributeToAnnotation::class);
        $this->attributesToAnnotations = $configuration;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Param|\PhpParser\Node\Stmt\Function_ $node
     */
    private function cleanupEmptyAttrGroups($node) : void
    {
        foreach ($node->attrGroups as $key => $attrGroup) {
            if ($attrGroup->attrs !== []) {
                continue;
            }
            unset($node->attrGroups[$key]);
            $this->isDowngraded = \true;
        }
    }
    /**
     * @param DowngradeAttributeToAnnotation[] $attributesToAnnotations
     */
    private function matchAttributeToAnnotation(Attribute $attribute, array $attributesToAnnotations) : ?DowngradeAttributeToAnnotation
    {
        foreach ($attributesToAnnotations as $attributeToAnnotation) {
            if (!$this->isName($attribute->name, $attributeToAnnotation->getAttributeClass())) {
                continue;
            }
            return $attributeToAnnotation;
        }
        return null;
    }
    private function shouldSkipAttribute(Attribute $attribute) : bool
    {
        $attributeName = $attribute->name->toString();
        return \in_array($attributeName, self::SKIPPED_ATTRIBUTES, \true);
    }
}

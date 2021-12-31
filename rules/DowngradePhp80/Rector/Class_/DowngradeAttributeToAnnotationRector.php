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
use Rector\PhpAttribute\Printer\DoctrineAnnotationFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211231\Webmozart\Assert\Assert;
/**
 * @changelog https://php.watch/articles/php-attributes#syntax
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\DowngradeAttributeToAnnotationRectorTest
 */
final class DowngradeAttributeToAnnotationRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const ATTRIBUTE_TO_ANNOTATION = 'attribute_to_annotation';
    /**
     * @var DowngradeAttributeToAnnotation[]
     */
    private $attributesToAnnotations = [];
    /**
     * @readonly
     * @var \Rector\PhpAttribute\Printer\DoctrineAnnotationFactory
     */
    private $doctrineAnnotationFactory;
    public function __construct(\Rector\PhpAttribute\Printer\DoctrineAnnotationFactory $doctrineAnnotationFactory)
    {
        $this->doctrineAnnotationFactory = $doctrineAnnotationFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor PHP attribute markers to annotations notation', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [new \Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation('Symfony\\Component\\Routing\\Annotation\\Route')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Stmt\Interface_::class, \PhpParser\Node\Param::class, \PhpParser\Node\Stmt\Function_::class];
    }
    /**
     * @param Class_|ClassMethod|Property|Interface_|Param|Function_  $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $key => $attribute) {
                $attributeToAnnotation = $this->matchAttributeToAnnotation($attribute, $this->attributesToAnnotations);
                if (!$attributeToAnnotation instanceof \Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation) {
                    continue;
                }
                unset($attrGroup->attrs[$key]);
                if (\strpos($attributeToAnnotation->getTag(), '\\') === \false) {
                    $phpDocInfo->addPhpDocTagNode(new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode('@' . $attributeToAnnotation->getTag(), new \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode('')));
                } else {
                    $doctrineAnnotation = $this->doctrineAnnotationFactory->createFromAttribute($attribute, $attributeToAnnotation->getTag());
                    $phpDocInfo->addTagValueNode($doctrineAnnotation);
                }
            }
        }
        // cleanup empty attr groups
        $this->cleanupEmptyAttrGroups($node);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $attributesToAnnotations = $configuration[self::ATTRIBUTE_TO_ANNOTATION] ?? $configuration;
        \RectorPrefix20211231\Webmozart\Assert\Assert::allIsAOf($attributesToAnnotations, \Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation::class);
        $this->attributesToAnnotations = $attributesToAnnotations;
    }
    /**
     * @param \PhpParser\Node\Param|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Property $node
     */
    private function cleanupEmptyAttrGroups($node) : void
    {
        foreach ($node->attrGroups as $key => $attrGroup) {
            if ($attrGroup->attrs !== []) {
                continue;
            }
            unset($node->attrGroups[$key]);
        }
    }
    /**
     * @param DowngradeAttributeToAnnotation[] $attributesToAnnotations
     */
    private function matchAttributeToAnnotation(\PhpParser\Node\Attribute $attribute, array $attributesToAnnotations) : ?\Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation
    {
        foreach ($attributesToAnnotations as $attributeToAnnotation) {
            if (!$this->isName($attribute->name, $attributeToAnnotation->getAttributeClass())) {
                continue;
            }
            return $attributeToAnnotation;
        }
        return null;
    }
}

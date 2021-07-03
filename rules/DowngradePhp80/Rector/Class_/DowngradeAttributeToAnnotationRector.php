<?php

declare(strict_types=1);

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
use Webmozart\Assert\Assert;

/**
 * @changelog https://php.watch/articles/php-attributes#syntax
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\DowngradeAttributeToAnnotationRectorTest
 */
final class DowngradeAttributeToAnnotationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ATTRIBUTE_TO_ANNOTATION = 'attribute_to_annotation';

    /**
     * @var DowngradeAttributeToAnnotation[]
     */
    private array $attributesToAnnotations = [];

    public function __construct(
        private DoctrineAnnotationFactory $doctrineAnnotationFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Refactor PHP attribute markers to annotations notation', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    #[Route(path: '/path', name: 'action')]
    public function action()
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
                ,
                [
                    self::ATTRIBUTE_TO_ANNOTATION => [
                        new DowngradeAttributeToAnnotation('Symfony\Component\Routing\Annotation\Route'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, ClassMethod::class, Property::class, Interface_::class, Param::class, Function_::class];
    }

    /**
     * @param Class_|ClassMethod|Property|Interface_|Param|Function_  $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $key => $attribute) {
                $attributeToAnnotation = $this->matchAttributeToAnnotation($attribute, $this->attributesToAnnotations);
                if (! $attributeToAnnotation instanceof DowngradeAttributeToAnnotation) {
                    continue;
                }

                unset($attrGroup->attrs[$key]);

                if (! \str_contains($attributeToAnnotation->getTag(), '\\')) {
                    $phpDocInfo->addPhpDocTagNode(
                        new PhpDocTagNode('@' . $attributeToAnnotation->getTag(), new GenericTagValueNode(''))
                    );
                } else {
                    $doctrineAnnotation = $this->doctrineAnnotationFactory->createFromAttribute(
                        $attribute,
                        $attributeToAnnotation->getTag()
                    );
                    $phpDocInfo->addTagValueNode($doctrineAnnotation);
                }
            }
        }

        // cleanup empty attr groups
        $this->cleanupEmptyAttrGroups($node);

        return $node;
    }

    /**
     * @param array<string, DowngradeAttributeToAnnotation[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $attributesToAnnotations = $configuration[self::ATTRIBUTE_TO_ANNOTATION] ?? [];
        Assert::allIsInstanceOf($attributesToAnnotations, DowngradeAttributeToAnnotation::class);

        $this->attributesToAnnotations = $attributesToAnnotations;
    }

    private function cleanupEmptyAttrGroups(
        ClassMethod | Property | Class_ | Interface_ | Param | Function_ $node
    ): void {
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
    private function matchAttributeToAnnotation(
        Attribute $attribute,
        array $attributesToAnnotations
    ): ?DowngradeAttributeToAnnotation {
        foreach ($attributesToAnnotations as $attributeToAnnotation) {
            if (! $this->isName($attribute->name, $attributeToAnnotation->getAttributeClass())) {
                continue;
            }

            return $attributeToAnnotation;
        }

        return null;
    }
}

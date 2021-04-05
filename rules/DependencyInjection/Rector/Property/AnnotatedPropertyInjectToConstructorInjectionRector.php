<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DependencyInjection\NodeAnalyzer\NetteInjectPropertyAnalyzer;
use Rector\DependencyInjection\TypeAnalyzer\InjectTagValueNodeToServiceTypeResolver;
use Rector\FamilyTree\NodeAnalyzer\PropertyUsageAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Can cover these cases:
 * - https://doc.nette.org/en/2.4/di-usage#toc-inject-annotations
 * - https://github.com/Kdyby/Autowired/blob/master/docs/en/index.md#autowired-properties
 * - http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * - https://github.com/rectorphp/rector/issues/700#issue-370301169
 * - https://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations#inject
 *
 * @see \Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\AnnotatedPropertyInjectToConstructorInjectionRectorTest
 */
final class AnnotatedPropertyInjectToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const INJECT_ANNOTATION_CLASSES = ['JMS\DiExtraBundle\Annotation\Inject', 'DI\Annotation\Inject'];

    /**
     * @var PropertyUsageAnalyzer
     */
    private $propertyUsageAnalyzer;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @var InjectTagValueNodeToServiceTypeResolver
     */
    private $injectTagValueNodeToServiceTypeResolver;

    /**
     * @var NetteInjectPropertyAnalyzer
     */
    private $netteInjectPropertyAnalyzer;

    /**
     * @var PhpDocTagRemover
     */
    private $phpDocTagRemover;

    public function __construct(
        PhpDocTypeChanger $phpDocTypeChanger,
        InjectTagValueNodeToServiceTypeResolver $injectTagValueNodeToServiceTypeResolver,
        PropertyUsageAnalyzer $propertyUsageAnalyzer,
        NetteInjectPropertyAnalyzer $netteInjectPropertyAnalyzer,
        PhpDocTagRemover $phpDocTagRemover
    ) {
        $this->propertyUsageAnalyzer = $propertyUsageAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->injectTagValueNodeToServiceTypeResolver = $injectTagValueNodeToServiceTypeResolver;
        $this->netteInjectPropertyAnalyzer = $netteInjectPropertyAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns properties with `@inject` to private properties and constructor injection',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
/**
 * @var SomeService
 * @inject
 */
public $someService;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
/**
 * @var SomeService
 */
private $someService;

public function __construct(SomeService $someService)
{
    $this->someService = $someService;
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        if ($phpDocInfo->hasByName('inject')) {
            if ($this->netteInjectPropertyAnalyzer->canBeRefactored($node, $phpDocInfo)) {
                return $this->refactorNetteInjectProperty($phpDocInfo, $node);
            }

            // is not refactorable
            return null;
        }

        foreach (self::INJECT_ANNOTATION_CLASSES as $injectAnnotationClass) {
            $injectTagNode = $phpDocInfo->getByAnnotationClass($injectAnnotationClass);
            if (! $injectTagNode instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }

            $serviceType = new MixedType();
            if ($injectTagNode !== null) {
                $serviceType = $phpDocInfo->getVarType();
            }

            if ($serviceType instanceof MixedType) {
                $serviceType = $this->injectTagValueNodeToServiceTypeResolver->resolve(
                    $node,
                    $phpDocInfo,
                    $injectTagNode
                );
            }

            if ($serviceType instanceof MixedType) {
                return null;
            }

            $this->refactorPropertyWithAnnotation($node, $serviceType, $injectTagNode);

            if ($this->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
                $this->removeNode($node);
                return null;
            }

            return $node;
        }

        return null;
    }

    private function refactorPropertyWithAnnotation(
        Property $property,
        Type $type,
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode
    ): void {
        $propertyName = $this->getName($property);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineAnnotationTagValueNode);

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $this->addConstructorDependencyToClass($classLike, $type, $propertyName, $property->flags);
    }

    private function refactorNetteInjectProperty(PhpDocInfo $phpDocInfo, Property $property): ?Property
    {
        $injectTagNode = $phpDocInfo->getByName('inject');
        if ($injectTagNode instanceof \PHPStan\PhpDocParser\Ast\Node) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $injectTagNode);
        }

        if ($this->propertyUsageAnalyzer->isPropertyFetchedInChildClass($property)) {
            $this->visibilityManipulator->makeProtected($property);
        } else {
            $this->visibilityManipulator->makePrivate($property);
        }

        $this->propertyAdder->addPropertyToCollector($property);

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->removeNode($property);
            return null;
        }

        return $property;
    }
}

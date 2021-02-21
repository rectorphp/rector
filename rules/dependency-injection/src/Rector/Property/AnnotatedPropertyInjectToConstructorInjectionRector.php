<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DependencyInjection\NodeAnalyzer\NetteInjectPropertyAnalyzer;
use Rector\DependencyInjection\TypeAnalyzer\InjectParameterAnalyzer;
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
 * @see \Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\AnnotatedPropertyInjectToConstructorInjectionRectorTest
 */
final class AnnotatedPropertyInjectToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var array<class-string<ShortNameAwareTagInterface>>
     */
    private const INJECT_TAG_VALUE_NODE_TYPES = [PHPDIInjectTagValueNode::class, JMSInjectTagValueNode::class];

    /**
     * @var PropertyUsageAnalyzer
     */
    private $propertyUsageAnalyzer;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @var InjectParameterAnalyzer
     */
    private $injectParameterAnalyzer;

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
        InjectParameterAnalyzer $injectParameterAnalyzer,
        InjectTagValueNodeToServiceTypeResolver $injectTagValueNodeToServiceTypeResolver,
        PropertyUsageAnalyzer $propertyUsageAnalyzer,
        NetteInjectPropertyAnalyzer $netteInjectPropertyAnalyzer,
        PhpDocTagRemover $phpDocTagRemover
    ) {
        $this->propertyUsageAnalyzer = $propertyUsageAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->injectParameterAnalyzer = $injectParameterAnalyzer;
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
     * @return string[]
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

        if ($this->netteInjectPropertyAnalyzer->detect($node, $phpDocInfo)) {
            return $this->refactorNetteInjectProperty($phpDocInfo, $node);
        }

        foreach (self::INJECT_TAG_VALUE_NODE_TYPES as $tagValueNodeType) {
            $injectTagValueNode = $phpDocInfo->getByType($tagValueNodeType);
            if ($injectTagValueNode === null) {
                continue;
            }

            if ($this->injectParameterAnalyzer->isParameterInject($injectTagValueNode)) {
                return null;
            }

            $serviceType = $this->injectTagValueNodeToServiceTypeResolver->resolve(
                $node,
                $phpDocInfo,
                $injectTagValueNode
            );
            if ($serviceType instanceof MixedType) {
                return null;
            }

            $this->refactorPropertyWithAnnotation($node, $serviceType, $injectTagValueNode);

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
        AbstractTagValueNode $tagValueNode
    ): void {
        $propertyName = $this->getName($property);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $tagValueNode);

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $this->addConstructorDependencyToClass($classLike, $type, $propertyName, $property->flags);
    }

    private function refactorNetteInjectProperty(PhpDocInfo $phpDocInfo, Property $property): ?Property
    {
        $phpDocInfo->removeByType(NetteInjectTagNode::class);

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

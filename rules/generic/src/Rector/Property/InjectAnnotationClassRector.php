<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Property;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DependencyInjection\TypeAnalyzer\JMSDITypeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations#inject
 *
 * @see \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\InjectAnnotationClassRectorTest
 */
final class InjectAnnotationClassRector extends AbstractRector
{
    /**
     * @var array<class-string<AbstractTagValueNode>>
     */
    private const ANNOTATION_TO_TAG_CLASS = [PHPDIInjectTagValueNode::class, JMSInjectTagValueNode::class];

    /**
     * @var string
     * @see https://regex101.com/r/pjusUN/1
     */
    private const BETWEEN_PERCENT_CHARS_REGEX = '#%(.*?)%#';

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @var JMSDITypeResolver
     */
    private $jmsDITypeResolver;

    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, JMSDITypeResolver $jmsDITypeResolver)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->jmsDITypeResolver = $jmsDITypeResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes properties with specified annotations class to constructor injection', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
/**
 * @DI\Inject("entity.manager")
 */
private $entityManager;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
/**
 * @var EntityManager
 */
private $entityManager;

public function __construct(EntityManager $entityManager)
{
    $this->entityManager = entityManager;
}
}
CODE_SAMPLE
            ),
        ]);
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        foreach (self::ANNOTATION_TO_TAG_CLASS as $tagClass) {
            $injectTagValueNode = $phpDocInfo->getByType($tagClass);
            if ($injectTagValueNode === null) {
                continue;
            }

            if ($this->isParameterInject($injectTagValueNode)) {
                return null;
            }

            $type = $this->resolveType($node, $injectTagValueNode);

            return $this->refactorPropertyWithAnnotation($node, $type, $tagClass);
        }

        return null;
    }

    private function isParameterInject(PhpDocTagValueNode $phpDocTagValueNode): bool
    {
        if (! $phpDocTagValueNode instanceof JMSInjectTagValueNode) {
            return false;
        }

        $serviceName = $phpDocTagValueNode->getServiceName();
        if ($serviceName === null) {
            return false;
        }

        return (bool) Strings::match($serviceName, self::BETWEEN_PERCENT_CHARS_REGEX);
    }

    private function resolveType(Property $property, PhpDocTagValueNode $phpDocTagValueNode): Type
    {
        if ($phpDocTagValueNode instanceof JMSInjectTagValueNode) {
            return $this->jmsDITypeResolver->resolve($property, $phpDocTagValueNode);
        }

        if ($phpDocTagValueNode instanceof PHPDIInjectTagValueNode) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            return $phpDocInfo->getVarType();
        }

        throw new ShouldNotHappenException();
    }

    private function refactorPropertyWithAnnotation(Property $property, Type $type, string $tagClass): ?Property
    {
        if ($type instanceof MixedType) {
            return null;
        }

        $propertyName = $this->getName($property);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
        $phpDocInfo->removeByType($tagClass);

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $this->addConstructorDependencyToClass($classLike, $type, $propertyName, $property->flags);

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::PROPERTY_PROMOTION)) {
            $this->removeNode($property);
            return null;
        }

        return $property;
    }
}

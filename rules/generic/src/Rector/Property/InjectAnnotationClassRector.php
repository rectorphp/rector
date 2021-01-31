<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Property;

use DI\Annotation\Inject as PHPDIInject;
use JMS\DiExtraBundle\Annotation\Inject as JMSInject;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\ServiceMapProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations#inject
 *
 * @see \Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector\InjectAnnotationClassRectorTest
 */
final class InjectAnnotationClassRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ANNOTATION_CLASSES = 'annotation_classes';

    /**
     * @var array<string, string>
     */
    private const ANNOTATION_TO_TAG_CLASS = [
        PHPDIInject::class => PHPDIInjectTagValueNode::class,
        JMSInject::class => JMSInjectTagValueNode::class,
    ];

    /**
     * @var string
     * @see https://regex101.com/r/pjusUN/1
     */
    private const BETWEEN_PERCENT_CHARS_REGEX = '#%(.*?)%#';

    /**
     * @var string[]
     */
    private $annotationClasses = [];

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var ServiceMapProvider
     */
    private $serviceMapProvider;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(
        ServiceMapProvider $serviceMapProvider,
        ErrorAndDiffCollector $errorAndDiffCollector,
        PhpDocTypeChanger $phpDocTypeChanger
    ) {
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->serviceMapProvider = $serviceMapProvider;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes properties with specified annotations class to constructor injection',
            [
                new ConfiguredCodeSample(
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
                    ,
                    [
                        self::ANNOTATION_CLASSES => [PHPDIInject::class, JMSInject::class],
                    ]
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
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($this->annotationClasses as $annotationClass) {
            $this->ensureAnnotationClassIsSupported($annotationClass);

            $tagClass = self::ANNOTATION_TO_TAG_CLASS[$annotationClass];

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

    public function configure(array $configuration): void
    {
        $this->annotationClasses = $configuration[self::ANNOTATION_CLASSES] ?? [];
    }

    private function ensureAnnotationClassIsSupported(string $annotationClass): void
    {
        if (isset(self::ANNOTATION_TO_TAG_CLASS[$annotationClass])) {
            return;
        }

        $availableAnnotations = array_keys(self::ANNOTATION_TO_TAG_CLASS);
        $errorMessage = sprintf(
            'Annotation class "%s" is not implemented yet. Use one of "%s" or add custom tag for it to Rector.',
            $annotationClass,
            implode('", "', $availableAnnotations)
        );

        throw new NotImplementedYetException($errorMessage);
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
            return $this->resolveJMSDIInjectType($property, $phpDocTagValueNode);
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

    private function resolveJMSDIInjectType(Property $property, JMSInjectTagValueNode $jmsInjectTagValueNode): Type
    {
        $serviceMap = $this->serviceMapProvider->provide();
        $serviceName = $jmsInjectTagValueNode->getServiceName();

        if ($serviceName) {
            if (class_exists($serviceName)) {
                // single class service
                return new ObjectType($serviceName);
            }

            // 2. service name
            if ($serviceMap->hasService($serviceName)) {
                $serviceType = $serviceMap->getServiceType($serviceName);
                if ($serviceType !== null) {
                    return $serviceType;
                }
            }
        }

        // 3. service is in @var annotation
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $varType = $phpDocInfo->getVarType();
        if (! $varType instanceof MixedType) {
            return $varType;
        }

        // the @var is missing and service name was not found → report it
        $this->reportServiceNotFound($serviceName, $property);

        return new MixedType();
    }

    private function reportServiceNotFound(?string $serviceName, Property $property): void
    {
        if ($serviceName !== null) {
            return;
        }

        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $property->getAttribute(AttributeKey::FILE_INFO);

        $errorMessage = sprintf('Service "%s" was not found in DI Container of your Symfony App.', $serviceName);

        $this->errorAndDiffCollector->addErrorWithRectorClassMessageAndFileInfo(
            self::class,
            $errorMessage,
            $fileInfo
        );
    }
}

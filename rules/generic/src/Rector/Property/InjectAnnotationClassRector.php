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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Symfony\ServiceMapProvider;
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
    public const ANNOTATION_CLASSES = '$annotationClasses';

    /**
     * @var string[]
     */
    private const ANNOTATION_TO_TAG_CLASS = [
        PHPDIInject::class => PHPDIInjectTagValueNode::class,
        JMSInject::class => JMSInjectTagValueNode::class,
    ];

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

    public function __construct(
        ServiceMapProvider $serviceMapProvider,
        ErrorAndDiffCollector $errorAndDiffCollector
    ) {
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->serviceMapProvider = $serviceMapProvider;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes properties with specified annotations class to constructor injection',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
use JMS\DiExtraBundle\Annotation as DI;

class SomeController
{
    /**
     * @DI\Inject("entity.manager")
     */
    private $entityManager;
}
PHP
                    ,
                    <<<'PHP'
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
PHP
                    ,
                    [
                        '$annotationClasses' => [PHPDIInject::class, JMSInject::class],
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
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

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

        throw new NotImplementedException(sprintf(
            'Annotation class "%s" is not implemented yet. Use one of "%s" or add custom tag for it to Rector.',
            $annotationClass,
            implode('", "', array_keys(self::ANNOTATION_TO_TAG_CLASS))
        ));
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

        return (bool) Strings::match($serviceName, '#%(.*?)%#');
    }

    private function resolveType(Node $node, PhpDocTagValueNode $phpDocTagValueNode): Type
    {
        if ($phpDocTagValueNode instanceof JMSInjectTagValueNode) {
            return $this->resolveJMSDIInjectType($node, $phpDocTagValueNode);
        }

        if ($phpDocTagValueNode instanceof PHPDIInjectTagValueNode) {
            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

            return $phpDocInfo->getVarType();
        }

        throw new ShouldNotHappenException();
    }

    private function refactorPropertyWithAnnotation(Property $property, Type $type, string $tagClass): ?Property
    {
        if ($type instanceof MixedType) {
            return null;
        }

        $name = $this->getName($property);
        if ($name === null) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->changeVarType($type);
        $phpDocInfo->removeByType($tagClass);

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $this->addPropertyToClass($classLike, $type, $name);

        return $property;
    }

    private function resolveJMSDIInjectType(Node $node, JMSInjectTagValueNode $jmsInjectTagValueNode): Type
    {
        $serviceMap = $this->serviceMapProvider->provide();
        $serviceName = $jmsInjectTagValueNode->getServiceName();

        if ($serviceName) {
            // 1. service class
            if (class_exists($serviceName)) {
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
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $varType = $phpDocInfo->getVarType();
        if (! $varType instanceof MixedType) {
            return $varType;
        }

        // the @var is missing and service name was not found → report it
        $this->reportServiceNotFound($serviceName, $node);

        return new MixedType();
    }

    private function reportServiceNotFound(?string $serviceName, Node $node): void
    {
        if ($serviceName !== null) {
            return;
        }

        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);

        $errorMessage = sprintf('Service "%s" was not found in DI Container of your Symfony App.', $serviceName);

        $this->errorAndDiffCollector->addErrorWithRectorClassMessageAndFileInfo(
            self::class,
            $errorMessage,
            $fileInfo
        );
    }
}

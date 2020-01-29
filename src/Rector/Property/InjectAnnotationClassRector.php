<?php

declare(strict_types=1);

namespace Rector\Rector\Property;

use DI\Annotation\Inject as PHPDIInject;
use JMS\DiExtraBundle\Annotation\Inject as JMSInject;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Application\ErrorAndDiffCollector;
use Rector\BetterPhpDocParser\PhpDocNode\JMS\JMSInjectTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Symfony\ServiceMapProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations#inject
 *
 * @see \Rector\Tests\Rector\Property\InjectAnnotationClassRector\InjectAnnotationClassRectorTest
 */
final class InjectAnnotationClassRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $annotationToTagClass = [
        PHPDIInject::class => PHPDIInjectTagValueNode::class,
        JMSInject::class => JMSInjectTagValueNode::class,
    ];

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var string[]
     */
    private $annotationClasses = [];

    /**
     * @var ServiceMapProvider
     */
    private $serviceMapProvider;

    /**
     * @param string[] $annotationClasses
     */
    public function __construct(
        ServiceMapProvider $serviceMapProvider,
        ErrorAndDiffCollector $errorAndDiffCollector,
        array $annotationClasses = []
    ) {
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->annotationClasses = $annotationClasses;
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
        $phpDocInfo = $this->getPhpDocInfo($node);
        if ($phpDocInfo === null) {
            return null;
        }

        foreach ($this->annotationClasses as $annotationClass) {
            $this->ensureAnnotationClassIsSupported($annotationClass);

            $tagClass = $this->annotationToTagClass[$annotationClass];

            $injectTagValueNode = $phpDocInfo->getByType($tagClass);
            if ($injectTagValueNode === null) {
                continue;
            }

            $type = $this->resolveType($node, $injectTagValueNode);
            return $this->refactorPropertyWithAnnotation($node, $type, $tagClass);
        }

        return null;
    }

    private function ensureAnnotationClassIsSupported(string $annotationClass): void
    {
        if (isset($this->annotationToTagClass[$annotationClass])) {
            return;
        }

        throw new NotImplementedException(sprintf(
            'Annotation class "%s" is not implemented yet. Use one of "%s" or add custom tag for it to Rector.',
            $annotationClass,
            implode('", "', array_keys($this->annotationToTagClass))
        ));
    }

    private function resolveType(Node $node, PhpDocTagValueNode $phpDocTagValueNode): Type
    {
        if ($phpDocTagValueNode instanceof JMSInjectTagValueNode) {
            return $this->resolveJMSDIInjectType($node, $phpDocTagValueNode);
        }

        if ($phpDocTagValueNode instanceof PHPDIInjectTagValueNode) {
            return $this->docBlockManipulator->getVarType($node);
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

        $this->docBlockManipulator->changeVarTag($property, $type);
        $this->docBlockManipulator->removeTagFromNode($property, $tagClass);

        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $this->addPropertyToClass($classNode, $type, $name);

        return $property;
    }

    private function resolveJMSDIInjectType(Node $node, JMSInjectTagValueNode $jmsInjectTagValueNode): Type
    {
        $serviceMap = $this->serviceMapProvider->provide();
        $serviceName = $jmsInjectTagValueNode->getServiceName();

        if ($serviceName) {
            if (class_exists($serviceName)) {
                return new ObjectType($serviceName);
            }

            if ($serviceMap->hasService($serviceName)) {
                $serviceType = $serviceMap->getServiceType($serviceName);
                if ($serviceType !== null) {
                    return $serviceType;
                }
            }
        }

        $varType = $this->docBlockManipulator->getVarType($node);
        if (! $varType instanceof MixedType) {
            return $varType;
        }

        // the @var is missing and service name was not found → report it
        if ($serviceName) {
            /** @var SmartFileInfo $fileInfo */
            $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);

            $this->errorAndDiffCollector->addErrorWithRectorClassMessageAndFileInfo(
                self::class,
                sprintf('Service "%s" was not found in DI Container of your Symfony App.', $serviceName),
                $fileInfo
            );
        }

        return new MixedType();
    }
}

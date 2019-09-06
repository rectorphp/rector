<?php declare(strict_types=1);

namespace Rector\Rector\Property;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Application\ErrorAndDiffCollector;
use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Throwable;

/**
 * @see https://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations#inject
 * @see \Rector\Tests\Rector\Property\InjectAnnotationClassRector\InjectAnnotationClassRectorTest
 */
final class InjectAnnotationClassRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var AnalyzedApplicationContainerInterface
     */
    private $analyzedApplicationContainer;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var string[]
     */
    private $annotationClasses = [];

    /**
     * @param string[] $annotationClasses
     */
    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        AnalyzedApplicationContainerInterface $analyzedApplicationContainer,
        ErrorAndDiffCollector $errorAndDiffCollector,
        array $annotationClasses = []
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->analyzedApplicationContainer = $analyzedApplicationContainer;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->annotationClasses = $annotationClasses;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
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
                        '$annotationClasses' => ['JMS\DiExtraBundle\Annotation\Inject'],
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
        foreach ($this->annotationClasses as $annotationClass) {
            if (! $this->docBlockManipulator->hasTag($node, $annotationClass)) {
                continue;
            }

            return $this->refactorPropertyWithAnnotation($node, $annotationClass);
        }

        return null;
    }

    private function resolveType(Node $node, string $annotationClass): Type
    {
        $injectTagNode = $this->docBlockManipulator->getTagByName($node, $annotationClass);

        $serviceName = $this->resolveServiceName($injectTagNode, $node);
        if ($serviceName) {
            try {
                if ($this->analyzedApplicationContainer->hasService($serviceName)) {
                    return $this->analyzedApplicationContainer->getTypeForName($serviceName);
                }
            } catch (Throwable $throwable) {
                // resolve later in errorAndDiffCollector if @var not found
            }
        }

        $varTypeInfo = $this->docBlockManipulator->getVarTypeInfo($node);
        if ($varTypeInfo !== null && $varTypeInfo->getFqnType() !== null) {
            // @todo resolve to property PHPStan type
            $cleanType = ltrim($varTypeInfo->getFqnType(), '\\');

            return new ObjectType($cleanType);
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

    private function resolveServiceName(PhpDocTagNode $phpDocTagNode, Node $node): ?string
    {
        $injectTagContent = (string) $phpDocTagNode->value;
        $match = Strings::match($injectTagContent, '#(\'|")(?<serviceName>.*?)(\'|")#');
        if ($match['serviceName']) {
            return $match['serviceName'];
        }

        $match = Strings::match($injectTagContent, '#(\'|")%(?<parameterName>.*?)%(\'|")#');
        // it's parameter, we don't resolve that here
        if (isset($match['parameterName'])) {
            return null;
        }

        return $this->getName($node);
    }

    private function refactorPropertyWithAnnotation(Property $property, string $annotationClass): ?Property
    {
        $type = $this->resolveType($property, $annotationClass);
        if ($type instanceof MixedType) {
            return null;
        }

        $name = $this->getName($property);
        if ($name === null) {
            return null;
        }

        if (! $this->docBlockManipulator->hasTag($property, 'var')) {
            $this->docBlockManipulator->changeVarTag($property, $type);
        }

        $this->docBlockManipulator->removeTagFromNode($property, $annotationClass);

        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
        }

        $this->addPropertyToClass($classNode, $type, $name);

        return $property;
    }
}

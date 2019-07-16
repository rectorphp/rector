<?php

declare(strict_types=1);

namespace Rector\Rector\Property;

use function array_unshift;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
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

final class InjectAnnotationsToConstantRector extends AbstractRector
{
    /**
     * @var string
     */
    private $constantNameThatContainsDependencyMap;

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
     * @var array
     */
    private $refactoredProperties = [];

    /**
     * @param string[] $annotationClasses
     */
    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        AnalyzedApplicationContainerInterface $analyzedApplicationContainer,
        ErrorAndDiffCollector $errorAndDiffCollector,
        array $annotationClasses = [],
        string $constantNameThatContainsDependencyMap = 'DEPENDENCIES'
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->analyzedApplicationContainer = $analyzedApplicationContainer;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->annotationClasses = $annotationClasses;
        $this->constantNameThatContainsDependencyMap = $constantNameThatContainsDependencyMap;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Collects all properties with specified annotations and add them to an associative array("propertyName" => "propertyType") in a protected constant.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityManagerInterface;

class SomeController
{
    /**
     * @Inject
     * 
     * @var EntityManagerInterface
     */
    private $entityManager;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityManagerInterface;

class SomeController
{
    protected const DEPENDENCIES = parent::DEPENDENCIES + ['entityManager' => EntityManagerInterface::class];
    
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    
}
CODE_SAMPLE
                    ,
                    [
                        '$annotationClasses' => ['Inject'],
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
        return [Class_::class];
    }

    /**
     * @param Class_|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->stmts as $statement) {
            if ($statement instanceof Property) {
                $this->refactorProperty($statement);
            }
        }

        //In case properties have been refactored, add a class constant with a map of 'propertyName' => 'className' entries
        if ($node instanceof Class_ && count($this->refactoredProperties) > 0) {
            array_unshift($node->stmts, $this->getClassConstantStatement($node));
            $this->refactoredProperties = []; // Reset the array of refactored properties for the next class to be processed
        }

        return $node;
    }

    private function refactorProperty(Property $property): ?Property
    {
        foreach ($this->annotationClasses as $annotationClass) {
            if (! $this->docBlockManipulator->hasTag($property, $annotationClass)) {
                continue;
            }

            return $this->refactorPropertyWithAnnotation($property, $annotationClass);
        }

        return null;
    }

    private function refactorPropertyWithAnnotation(Property $property, string $annotationClass): ?Property
    {
        $type = $this->resolveType($property, $annotationClass);
        if ($type === null) {
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
            throw new ShouldNotHappenException();
        }

        $this->markPropertyAsBeingRefactored($type, $name);

        return $property;
    }

    private function resolveType(Node $node, string $annotationClass): ?string
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
            return ltrim($varTypeInfo->getFqnType(), '\\');
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

        return null;
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

    private function markPropertyAsBeingRefactored(string $propertyType, string $propertyName): void
    {
        $this->refactoredProperties[$propertyName] = $propertyType;
    }

    private function getClassConstantStatement(Class_ $classNode): Node\Stmt\ClassConst
    {
        return new Node\Stmt\ClassConst($this->getConstants($classNode), Class_::MODIFIER_PROTECTED);
    }

    private function getConstants(Class_ $classNode): array
    {
        return [$this->getConstantNode($classNode)];
    }

    private function getConstantNode(Class_ $classNode): Node\Const_
    {
        $constantValue = $classNode->extends === null ? $this->getArray() : $this->getBinaryPlusOperation();

        return new Node\Const_($this->constantNameThatContainsDependencyMap, $constantValue);
    }

    private function getArray(): Node\Expr\Array_
    {
        return new Node\Expr\Array_($this->getArrayItems());
    }

    private function getArrayItems(): array
    {
        $result = [];
        foreach ($this->refactoredProperties as $propertyName => $className) {
            $result[] = $this->getArrayItem($propertyName, $className);
        }

        return $result;
    }

    private function getArrayItem(string $propertyName, string $className): Node\Expr\ArrayItem
    {
        $key = new Node\Scalar\String_($propertyName);
        $absoluteFullyQualifiedClassName = '\\' . $className;
        $value = new Node\Expr\ClassConstFetch(
            new Node\Name($absoluteFullyQualifiedClassName),
            new Node\Identifier('class')
        );

        return new Node\Expr\ArrayItem($value, $key);
    }

    private function getBinaryPlusOperation(): Node\Expr\BinaryOp\Plus
    {
        return new Node\Expr\BinaryOp\Plus($this->getParentClassConstantStatement(), $this->getArray());
    }

    private function getParentClassConstantStatement(): Node\Expr\ClassConstFetch
    {
        return new Node\Expr\ClassConstFetch(new Node\Name('parent'), new Node\Identifier(
            $this->constantNameThatContainsDependencyMap
        ));
    }
}

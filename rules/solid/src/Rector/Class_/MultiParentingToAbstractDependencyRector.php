<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeFinder\ClassLikeParsedNodesFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\SOLID\NodeFactory\InjectMethodFactory;
use Rector\SOLID\NodeRemover\ClassMethodNodeRemover;

/**
 * @see \Rector\SOLID\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\MultiParentingToAbstractDependencyRectorTest
 */
final class MultiParentingToAbstractDependencyRector extends AbstractRector
{
    /**
     * @var string
     */
    public const FRAMEWORK_SYMFONY = 'symfony';

    /**
     * @var string
     */
    private const CONSTRUCTOR_METHOD_NAME = '__construct';

    /**
     * @var string
     */
    private $framework;

    /**
     * @var ObjectType[]
     */
    private $objectTypesToInject = [];

    /**
     * @var ClassMethodNodeRemover
     */
    private $classMethodNodeRemover;

    /**
     * @var InjectMethodFactory
     */
    private $injectMethodFactory;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    public function __construct(
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder,
        ClassMethodNodeRemover $classMethodNodeRemover,
        InjectMethodFactory $injectMethodFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        ClassInsertManipulator $classInsertManipulator,
        string $framework = ''
    ) {
        $this->framework = $framework;
        $this->classLikeParsedNodesFinder = $classLikeParsedNodesFinder;
        $this->injectMethodFactory = $injectMethodFactory;
        $this->classMethodNodeRemover = $classMethodNodeRemover;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classInsertManipulator = $classInsertManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Move dependency passed to all children to parent as @inject/@required dependency',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
abstract class AbstractParentClass
{
    private $someDependency;

    public function __construct(SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

class FirstChild extends AbstractParentClass
{
    public function __construct(SomeDependency $someDependency)
    {
        parent::__construct($someDependency);
    }
}

class SecondChild extends AbstractParentClass
{
    public function __construct(SomeDependency $someDependency)
    {
        parent::__construct($someDependency);
    }
}
PHP
,
                    <<<'PHP'
abstract class AbstractParentClass
{
    /**
     * @inject
     * @var SomeDependency
     */
    public $someDependency;
}

class FirstChild extends AbstractParentClass
{
}

class SecondChild extends AbstractParentClass
{
}
PHP
,
                    ['$framework' => 'nette']
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
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->isAbstract()) {
            return null;
        }

        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        $childrenClasses = $this->classLikeParsedNodesFinder->findChildrenOfClass($className);
        if (count($childrenClasses) < 2) {
            return null;
        }

        $constructMethod = $node->getMethod(self::CONSTRUCTOR_METHOD_NAME);
        if ($constructMethod === null) {
            return null;
        }

        $abstractClassConstructorParamTypes = $this->resolveConstructorParamClassTypes($node);

        // process
        $this->objectTypesToInject = [];

        foreach ($childrenClasses as $childrenClass) {
            $constructorClassMethod = $childrenClass->getMethod(self::CONSTRUCTOR_METHOD_NAME);
            if ($constructorClassMethod === null) {
                continue;
            }

            $this->refactorChildConstructorClassMethod($constructorClassMethod, $abstractClassConstructorParamTypes);

            $this->classMethodNodeRemover->removeClassMethodIfUseless($constructorClassMethod);
        }

        // 2. remove from abstract class
        $this->clearAbstractClassConstructor($constructMethod);

        // 3. add inject*/@required to abstract property
        $this->addInjectOrRequiredClassMethod($node);

        return $node;
    }

    /**
     * @return ObjectType[]
     */
    private function resolveConstructorParamClassTypes(Class_ $class): array
    {
        $constructMethod = $class->getMethod(self::CONSTRUCTOR_METHOD_NAME);
        if ($constructMethod === null) {
            return [];
        }

        $objectTypes = [];
        foreach ($constructMethod->getParams() as $param) {
            $paramType = $this->getObjectType($param);
            $paramType = $this->popFirstObjectTypeFromUnionType($paramType);

            if (! $paramType instanceof ObjectType) {
                continue;
            }

            $objectTypes[] = $paramType;
        }

        return $objectTypes;
    }

    private function addInjectOrRequiredClassMethod(Class_ $class): void
    {
        /** @var string $className */
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);

        if (count($this->objectTypesToInject) === 0) {
            return;
        }

        $injectClassMethod = $this->injectMethodFactory->createFromTypes(
            $this->objectTypesToInject,
            $className,
            $this->framework
        );

        $this->classInsertManipulator->addAsFirstMethod($class, $injectClassMethod);
    }

    private function clearAbstractClassConstructor(ClassMethod $classMethod): void
    {
        foreach ($classMethod->getParams() as $key => $param) {
            if (! $this->isObjectTypes($param, $this->objectTypesToInject)) {
                continue;
            }

            unset($classMethod->params[$key]);
            $this->classMethodNodeRemover->removeParamFromMethodBody($classMethod, $param);
        }

        $this->classMethodNodeRemover->removeClassMethodIfUseless($classMethod);
    }

    /**
     * @param ObjectType[] $abstractClassConstructorParamTypes
     */
    private function refactorChildConstructorClassMethod(
        ClassMethod $classMethod,
        array $abstractClassConstructorParamTypes
    ): void {
        foreach ($classMethod->getParams() as $key => $param) {
            $paramType = $this->getStaticType($param);
            $paramType = $this->popFirstObjectTypeFromUnionType($paramType);

            if (! $paramType instanceof ObjectType) {
                continue;
            }

            if (! $this->isSameObjectTypes($paramType, $abstractClassConstructorParamTypes)) {
                continue;
            }

            unset($classMethod->params[$key]);
            $this->classMethodNodeRemover->removeParamFromMethodBody($classMethod, $param);

            $this->objectTypesToInject[] = $paramType;
        }
    }

    private function popFirstObjectTypeFromUnionType(Type $paramType): Type
    {
        if (! $paramType instanceof UnionType) {
            return $paramType;
        }

        foreach ($paramType->getTypes() as $unionedType) {
            if ($unionedType instanceof ObjectType) {
                return $unionedType;
            }
        }

        return $paramType;
    }
}

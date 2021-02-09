<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\FrameworkName;
use Rector\Core\ValueObject\MethodName;
use Rector\DependencyInjection\NodeFactory\InjectMethodFactory;
use Rector\DependencyInjection\NodeRemover\ClassMethodNodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\DependencyInjection\Tests\Rector\Class_\MultiParentingToAbstractDependencyRector\MultiParentingToAbstractDependencyRectorTest
 */
final class MultiParentingToAbstractDependencyRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const FRAMEWORK = 'framework';

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
        ClassMethodNodeRemover $classMethodNodeRemover,
        InjectMethodFactory $injectMethodFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        ClassInsertManipulator $classInsertManipulator
    ) {
        $this->injectMethodFactory = $injectMethodFactory;
        $this->classMethodNodeRemover = $classMethodNodeRemover;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classInsertManipulator = $classInsertManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move dependency passed to all children to parent as @inject/@required dependency',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
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
CODE_SAMPLE
,
                    [
                        self::FRAMEWORK => FrameworkName::NETTE,
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

        $childrenClasses = $this->nodeRepository->findChildrenOfClass($className);
        if (count($childrenClasses) < 2) {
            return null;
        }

        $classMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        $abstractClassConstructorParamTypes = $this->resolveConstructorParamClassTypes($node);

        // process
        $this->objectTypesToInject = [];

        foreach ($childrenClasses as $childrenClass) {
            $constructorClassMethod = $childrenClass->getMethod(MethodName::CONSTRUCT);
            if (! $constructorClassMethod instanceof ClassMethod) {
                continue;
            }

            $this->refactorChildConstructorClassMethod($constructorClassMethod, $abstractClassConstructorParamTypes);

            $this->classMethodNodeRemover->removeClassMethodIfUseless($constructorClassMethod);
        }

        // 2. remove from abstract class
        $this->clearAbstractClassConstructor($classMethod);

        // 3. add inject*/@required to abstract property
        $this->addInjectOrRequiredClassMethod($node);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->framework = $configuration[self::FRAMEWORK];
    }

    /**
     * @return ObjectType[]
     */
    private function resolveConstructorParamClassTypes(Class_ $class): array
    {
        $constructorClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $constructorClassMethod instanceof ClassMethod) {
            return [];
        }

        $objectTypes = [];
        foreach ($constructorClassMethod->getParams() as $param) {
            $paramType = $this->getObjectType($param);
            $paramType = $this->popFirstObjectTypeFromUnionType($paramType);

            if (! $paramType instanceof ObjectType) {
                continue;
            }

            $objectTypes[] = $paramType;
        }

        return $objectTypes;
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

            if (! $this->nodeTypeResolver->isSameObjectTypes($paramType, $abstractClassConstructorParamTypes)) {
                continue;
            }

            $this->nodeRemover->removeParam($classMethod, $key);
            $this->classMethodNodeRemover->removeParamFromMethodBody($classMethod, $param);

            $this->objectTypesToInject[] = $paramType;
        }
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

    private function addInjectOrRequiredClassMethod(Class_ $class): void
    {
        /** @var string $className */
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);

        if ($this->objectTypesToInject === []) {
            return;
        }

        $injectClassMethod = $this->injectMethodFactory->createFromTypes(
            $this->objectTypesToInject,
            $className,
            $this->framework
        );

        $this->classInsertManipulator->addAsFirstMethod($class, $injectClassMethod);
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

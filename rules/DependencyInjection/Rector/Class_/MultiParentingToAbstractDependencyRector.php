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
 * @see \Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\MultiParentingToAbstractDependencyRectorTest
 */
final class MultiParentingToAbstractDependencyRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const FRAMEWORK = 'framework';

    private string $framework = FrameworkName::SYMFONY;

    /**
     * @var ObjectType[]
     */
    private array $injectObjectTypes = [];

    public function __construct(
        private ClassMethodNodeRemover $classMethodNodeRemover,
        private InjectMethodFactory $injectMethodFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        private ClassInsertManipulator $classInsertManipulator
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
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
     * @return array<class-string<Node>>
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
        $this->injectObjectTypes = [];

        foreach ($childrenClasses as $childClass) {
            $constructorClassMethod = $childClass->getMethod(MethodName::CONSTRUCT);
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

    /**
     * @param array<string, string> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->framework = $configuration[self::FRAMEWORK] ?? FrameworkName::SYMFONY;
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

            $this->injectObjectTypes[] = $paramType;
        }
    }

    private function clearAbstractClassConstructor(ClassMethod $classMethod): void
    {
        foreach ($classMethod->getParams() as $key => $param) {
            if (! $this->nodeTypeResolver->isObjectTypes($param, $this->injectObjectTypes)) {
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

        if ($this->injectObjectTypes === []) {
            return;
        }

        $injectClassMethod = $this->injectMethodFactory->createFromTypes(
            $this->injectObjectTypes,
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

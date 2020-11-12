<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Node;

use Nette\Utils\Strings;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Builder\MethodBuilder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
use Rector\SymfonyPHPUnit\Naming\ServiceNaming;

final class KernelTestCaseNodeFactory
{
    /**
     * @var PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ServiceNaming
     */
    private $serviceNaming;

    public function __construct(
        NodeFactory $nodeFactory,
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        ServiceNaming $serviceNaming
    ) {
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
        $this->nodeFactory = $nodeFactory;
        $this->serviceNaming = $serviceNaming;
    }

    /**
     * @param string[] $serviceTypes
     */
    public function createSetUpClassMethodWithGetTypes(Class_ $class, array $serviceTypes): ?ClassMethod
    {
        $assigns = $this->createSelfContainerGetWithTypeAssigns($class, $serviceTypes);
        if ($assigns === []) {
            return null;
        }

        $stmts = array_merge([new StaticCall(new Name('parent'), MethodName::SET_UP)], $assigns);

        $classMethodBuilder = new MethodBuilder(MethodName::SET_UP);
        $classMethodBuilder->makeProtected();
        $classMethodBuilder->addStmts($stmts);

        $classMethod = $classMethodBuilder->getNode();

        $this->phpUnitTypeDeclarationDecorator->decorate($classMethod);

        return $classMethod;
    }

    /**
     * @param string[] $serviceTypes
     *
     * @return Property[]
     */
    public function createPrivatePropertiesFromTypes(Class_ $class, array $serviceTypes): array
    {
        $properties = [];

        /** @var string $className */
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($serviceTypes as $serviceType) {
            $propertyName = $this->serviceNaming->resolvePropertyNameFromServiceType($serviceType);

            // skip existing properties
            if (property_exists($className, $propertyName)) {
                continue;
            }

            $serviceType = new ObjectType($serviceType);
            $properties[] = $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $serviceType);
        }

        return $properties;
    }

    /**
     * @param string[] $serviceTypes
     *
     * @return Expression[]
     *
     * E.g. "['SomeService']" → "$this->someService = self::$container->get(SomeService::class);"
     */
    public function createSelfContainerGetWithTypeAssigns(Class_ $class, array $serviceTypes): array
    {
        $stmts = [];

        /** @var string $className */
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($serviceTypes as $serviceType) {
            $propertyName = $this->serviceNaming->resolvePropertyNameFromServiceType($serviceType);
            // skip existing properties
            if (property_exists($className, $propertyName)) {
                continue;
            }

            $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
            $methodCall = $this->createSelfContainerGetWithTypeMethodCall($serviceType);

            $assign = new Assign($propertyFetch, $methodCall);
            $stmts[] = new Expression($assign);
        }
        return $stmts;
    }

    private function createSelfContainerGetWithTypeMethodCall(string $serviceType): MethodCall
    {
        $staticPropertyFetch = new StaticPropertyFetch(new Name('self'), 'container');
        $methodCall = new MethodCall($staticPropertyFetch, 'get');

        if (Strings::contains($serviceType, '_') && ! Strings::contains($serviceType, '\\')) {
            // keep string
            $getArgumentValue = new String_($serviceType);
        } else {
            $getArgumentValue = new ClassConstFetch(new FullyQualified($serviceType), 'class');
        }

        $methodCall->args[] = new Arg($getArgumentValue);

        return $methodCall;
    }
}

<?php

namespace Roave\BetterReflection\SourceLocator\Reflection;

use PhpParser\Builder\Class_;
use PhpParser\Builder\Declaration;
use PhpParser\Builder\Interface_;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\Builder\Trait_;
use PhpParser\BuilderAbstract;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TraitUseAdaptation;
use PhpParser\NodeAbstract;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass as CoreReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod as CoreReflectionMethod;
use ReflectionParameter;
use ReflectionProperty as CoreReflectionProperty;
use ReflectionType as CoreReflectionType;
use Reflector as CoreReflector;

/**
 * Function that generates a stub source from a given reflection instance.
 *
 * @internal
 */
final class SourceStubber
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var Standard
     */
    private $prettyPrinter;

    public function __construct()
    {
        $this->builderFactory = new BuilderFactory();
        $this->prettyPrinter  = new Standard(['shortArraySyntax' => true]);
    }

    public function __invoke(CoreReflectionClass $classReflection) : string
    {
        $classNode = $this->createClass($classReflection);

        $this->addClassModifiers($classNode, $classReflection);
        $this->addDocComment($classNode, $classReflection);
        $this->addExtendsAndImplements($classNode, $classReflection);
        $this->addTraitUse($classNode, $classReflection);
        $this->addProperties($classNode, $classReflection);
        $this->addConstants($classNode, $classReflection);
        $this->addMethods($classNode, $classReflection);

        if ( ! $classReflection->inNamespace()) {
            return $this->prettyPrinter->prettyPrint([$classNode->getNode()]);
        }

        $namespaceNode = $this->builderFactory->namespace($classReflection->getNamespaceName());
        $namespaceNode->addStmt($classNode);

        return $this->prettyPrinter->prettyPrint([$namespaceNode->getNode()]);
    }

    private function createClass(CoreReflectionClass $classReflection) : Declaration
    {
        if ($classReflection->isTrait()) {
            return $this->builderFactory->trait($classReflection->getShortName());
        }

        if ($classReflection->isInterface()) {
            return $this->builderFactory->interface($classReflection->getShortName());
        }

        return $this->builderFactory->class($classReflection->getShortName());
    }

    /**
     * @param Class_|Interface_|Trait_|Method|Property $node
     * @param CoreReflectionClass|CoreReflectionMethod|CoreReflectionProperty $reflection
     */
    private function addDocComment(BuilderAbstract $node, CoreReflector $reflection) : void
    {
        if (false !== $reflection->getDocComment()) {
            $node->setDocComment(new Doc($reflection->getDocComment()));
        }
    }

    /**
     * @param Class_|Interface_|Trait_ $classNode
     * @param CoreReflectionClass $classReflection
     */
    private function addClassModifiers(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        if ( ! $classReflection->isInterface() && $classReflection->isAbstract()) {
            // Interface \Iterator is interface and abstract
            $classNode->makeAbstract();
        }

        if ($classReflection->isFinal()) {
            $classNode->makeFinal();
        }
    }

    /**
     * @param Class_|Interface_|Trait_ $classNode
     * @param CoreReflectionClass $classReflection
     */
    private function addExtendsAndImplements(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        $parentClass = $classReflection->getParentClass();
        $interfaces  = $classReflection->getInterfaceNames();

        if ($parentClass) {
            $classNode->extend(new FullyQualified($parentClass->getName()));
            $interfaces = \array_diff($interfaces, $parentClass->getInterfaceNames());
        }

        foreach ($classReflection->getInterfaces() as $interface) {
            $interfaces = \array_diff($interfaces, $interface->getInterfaceNames());
        }

        foreach ($interfaces as $interfaceName) {
            if ($classReflection->isInterface()) {
                $classNode->extend(new FullyQualified($interfaceName));
            } else {
                $classNode->implement(new FullyQualified($interfaceName));
            }
        }
    }

    private function addTraitUse(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        $alreadyUsedTraitNames = [];

        foreach ($classReflection->getTraitAliases() as $methodNameAlias => $methodInfo) {
            [$traitName, $methodName] = \explode('::', $methodInfo);
            $traitUseNode             = new TraitUse(
                [new FullyQualified($traitName)],
                [new TraitUseAdaptation\Alias(new FullyQualified($traitName), $methodName, null, $methodNameAlias)]
            );

            $classNode->addStmt($traitUseNode);

            $alreadyUsedTraitNames[] = $traitName;
        }

        foreach (\array_diff($classReflection->getTraitNames(), $alreadyUsedTraitNames) as $traitName) {
            $classNode->addStmt(new TraitUse([new FullyQualified($traitName)]));
        }
    }

    private function addProperties(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        $defaultProperties = $classReflection->getDefaultProperties();

        foreach ($classReflection->getProperties() as $propertyReflection) {
            if ( ! $this->isPropertyDeclaredInClass($propertyReflection, $classReflection)) {
                continue;
            }

            $propertyNode = $this->builderFactory->property($propertyReflection->getName());

            $this->addPropertyModifiers($propertyNode, $propertyReflection);
            $this->addDocComment($propertyNode, $propertyReflection);

            if (\array_key_exists($propertyReflection->getName(), $defaultProperties)) {
                $propertyNode->setDefault($defaultProperties[$propertyReflection->getName()]);
            }

            $classNode->addStmt($propertyNode);
        }
    }

    private function isPropertyDeclaredInClass(CoreReflectionProperty $propertyReflection, CoreReflectionClass $classReflection) : bool
    {
        if ($propertyReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
            return false;
        }

        foreach ($classReflection->getTraits() as $trait) {
            if ($trait->hasProperty($propertyReflection->getName())) {
                return false;
            }
        }

        return true;
    }

    private function addPropertyModifiers(Property $propertyNode, CoreReflectionProperty $propertyReflection) : void
    {
        if ($propertyReflection->isStatic()) {
            $propertyNode->makeStatic();
        }

        if ($propertyReflection->isPublic()) {
            $propertyNode->makePublic();
        }

        if ($propertyReflection->isProtected()) {
            $propertyNode->makeProtected();
        }

        if ($propertyReflection->isPrivate()) {
            $propertyNode->makePrivate();
        }
    }

    private function addConstants(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        foreach ($classReflection->getReflectionConstants() as $constantReflection) {
            if ($constantReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
                continue;
            }

            $classConstantNode = new ClassConst(
                [new Const_($constantReflection->getName(), $this->constantValueNode($constantReflection))],
                $this->constantVisibilityFlags($constantReflection)
            );

            if (false !== $constantReflection->getDocComment()) {
                $classConstantNode->setDocComment(new Doc($constantReflection->getDocComment()));
            }

            $classNode->addStmt($classConstantNode);
        }
    }

    /**
     * A little hack so we don't have to copy the code in PhpParser\BuilderAbstract::normalizeValue()
     */
    private function constantValueNode(ReflectionClassConstant $constant) : ?Expr
    {
        return $this
            ->builderFactory
            ->property('')
            ->setDefault($constant->getValue())
            ->getNode()
            ->props[0]
            ->default;
    }

    private function constantVisibilityFlags(ReflectionClassConstant $constant) : int
    {
        if ($constant->isPrivate()) {
            return ClassNode::MODIFIER_PRIVATE;
        }

        if ($constant->isProtected()) {
            return ClassNode::MODIFIER_PROTECTED;
        }

        return ClassNode::MODIFIER_PUBLIC;
    }

    private function addMethods(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        foreach ($classReflection->getMethods() as $methodReflection) {
            if ( ! $this->isMethodDeclaredInClass($methodReflection, $classReflection)) {
                continue;
            }

            $methodNode = $this->builderFactory->method($methodReflection->getName());

            $this->addMethodFlags($methodNode, $methodReflection);
            $this->addDocComment($methodNode, $methodReflection);
            $this->addParameters($methodNode, $methodReflection);

            $returnType = $methodReflection->getReturnType();

            if (null !== $methodReflection->getReturnType()) {
                $methodNode->setReturnType($this->formatType($returnType));
            }

            $classNode->addStmt($methodNode);
        }
    }

    private function isMethodDeclaredInClass(CoreReflectionMethod $methodReflection, CoreReflectionClass $classReflection) : bool
    {
        if ($methodReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
            return false;
        }

        if (\array_key_exists($methodReflection->getName(), $classReflection->getTraitAliases())) {
            return false;
        }

        foreach ($classReflection->getTraits() as $trait) {
            if ($trait->hasMethod($methodReflection->getName())) {
                return false;
            }
        }

        return true;
    }

    private function addMethodFlags(Method $methodNode, CoreReflectionMethod $methodReflection) : void
    {
        if ($methodReflection->isFinal()) {
            $methodNode->makeFinal();
        }

        if ($methodReflection->isAbstract()) {
            $methodNode->makeAbstract();
        }

        if ($methodReflection->isStatic()) {
            $methodNode->makeStatic();
        }

        if ($methodReflection->isPublic()) {
            $methodNode->makePublic();
        }

        if ($methodReflection->isProtected()) {
            $methodNode->makeProtected();
        }

        if ($methodReflection->isPrivate()) {
            $methodNode->makePrivate();
        }

        if ($methodReflection->returnsReference()) {
            $methodNode->makeReturnByRef();
        }
    }

    private function addParameters(Method $methodNode, CoreReflectionMethod $methodReflection) : void
    {
        foreach ($methodReflection->getParameters() as $parameterReflection) {
            $parameterNode = $this->builderFactory->param($parameterReflection->getName());

            $this->addParameterModifiers($parameterReflection, $parameterNode);

            if ($parameterReflection->isOptional() && ! $parameterReflection->isVariadic()) {
                $parameterNode->setDefault($this->parameterDefaultValue($parameterReflection, $methodReflection));
            }

            $methodNode->addParam($this->addParameterModifiers($parameterReflection, $parameterNode));
        }
    }

    private function addParameterModifiers(ReflectionParameter $parameterReflection, Param $parameterNode) : Param
    {
        if ($parameterReflection->isVariadic()) {
            $parameterNode->makeVariadic();
        }

        if ($parameterReflection->isPassedByReference()) {
            $parameterNode->makeByRef();
        }

        $parameterType = $parameterReflection->getType();

        if (null !== $parameterReflection->getType()) {
            $parameterNode->setType($this->formatType($parameterType));
        }

        return $parameterNode;
    }

    /**
     * @return mixed
     */
    private function parameterDefaultValue(
        ReflectionParameter $parameterReflection,
        CoreReflectionMethod $methodReflection
    ) {
        if ($methodReflection->getDeclaringClass()->isInternal()) {
            return null;
        }

        return $parameterReflection->getDefaultValue();
    }

    /**
     * @param CoreReflectionType $type
     * @return NodeAbstract
     */
    private function formatType(CoreReflectionType $type) : NodeAbstract
    {
        $name     = (string) $type;
        $nameNode = $type->isBuiltin() || \in_array($name, ['self', 'parent'], true) ? new Name($name) : new FullyQualified($name);
        return $type->allowsNull() ? new NullableType($nameNode) : $nameNode;
    }
}

?>
-----
<?php

namespace Roave\BetterReflection\SourceLocator\Reflection;

use PhpParser\Builder\Class_;
use PhpParser\Builder\Declaration;
use PhpParser\Builder\Interface_;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\Builder\Trait_;
use \PhpParser\Builder;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TraitUseAdaptation;
use PhpParser\NodeAbstract;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass as CoreReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod as CoreReflectionMethod;
use ReflectionParameter;
use ReflectionProperty as CoreReflectionProperty;
use ReflectionType as CoreReflectionType;
use Reflector as CoreReflector;

/**
 * Function that generates a stub source from a given reflection instance.
 *
 * @internal
 */
final class SourceStubber
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var Standard
     */
    private $prettyPrinter;

    public function __construct()
    {
        $this->builderFactory = new BuilderFactory();
        $this->prettyPrinter  = new Standard(['shortArraySyntax' => true]);
    }

    public function __invoke(CoreReflectionClass $classReflection) : string
    {
        $classNode = $this->createClass($classReflection);

        $this->addClassModifiers($classNode, $classReflection);
        $this->addDocComment($classNode, $classReflection);
        $this->addExtendsAndImplements($classNode, $classReflection);
        $this->addTraitUse($classNode, $classReflection);
        $this->addProperties($classNode, $classReflection);
        $this->addConstants($classNode, $classReflection);
        $this->addMethods($classNode, $classReflection);

        if ( ! $classReflection->inNamespace()) {
            return $this->prettyPrinter->prettyPrint([$classNode->getNode()]);
        }

        $namespaceNode = $this->builderFactory->namespace($classReflection->getNamespaceName());
        $namespaceNode->addStmt($classNode);

        return $this->prettyPrinter->prettyPrint([$namespaceNode->getNode()]);
    }

    private function createClass(CoreReflectionClass $classReflection) : Declaration
    {
        if ($classReflection->isTrait()) {
            return $this->builderFactory->trait($classReflection->getShortName());
        }

        if ($classReflection->isInterface()) {
            return $this->builderFactory->interface($classReflection->getShortName());
        }

        return $this->builderFactory->class($classReflection->getShortName());
    }

    /**
     * @param Class_|Interface_|Trait_|Method|Property $node
     * @param CoreReflectionClass|CoreReflectionMethod|CoreReflectionProperty $reflection
     */
    private function addDocComment(\PhpParser\Builder $node, CoreReflector $reflection) : void
    {
        if (false !== $reflection->getDocComment()) {
            $node->setDocComment(new Doc($reflection->getDocComment()));
        }
    }

    /**
     * @param Class_|Interface_|Trait_ $classNode
     * @param CoreReflectionClass $classReflection
     */
    private function addClassModifiers(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        if ( ! $classReflection->isInterface() && $classReflection->isAbstract()) {
            // Interface \Iterator is interface and abstract
            $classNode->makeAbstract();
        }

        if ($classReflection->isFinal()) {
            $classNode->makeFinal();
        }
    }

    /**
     * @param Class_|Interface_|Trait_ $classNode
     * @param CoreReflectionClass $classReflection
     */
    private function addExtendsAndImplements(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        $parentClass = $classReflection->getParentClass();
        $interfaces  = $classReflection->getInterfaceNames();

        if ($parentClass) {
            $classNode->extend(new FullyQualified($parentClass->getName()));
            $interfaces = \array_diff($interfaces, $parentClass->getInterfaceNames());
        }

        foreach ($classReflection->getInterfaces() as $interface) {
            $interfaces = \array_diff($interfaces, $interface->getInterfaceNames());
        }

        foreach ($interfaces as $interfaceName) {
            if ($classReflection->isInterface()) {
                $classNode->extend(new FullyQualified($interfaceName));
            } else {
                $classNode->implement(new FullyQualified($interfaceName));
            }
        }
    }

    private function addTraitUse(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        $alreadyUsedTraitNames = [];

        foreach ($classReflection->getTraitAliases() as $methodNameAlias => $methodInfo) {
            [$traitName, $methodName] = \explode('::', $methodInfo);
            $traitUseNode             = new TraitUse(
                [new FullyQualified($traitName)],
                [new TraitUseAdaptation\Alias(new FullyQualified($traitName), $methodName, null, $methodNameAlias)]
            );

            $classNode->addStmt($traitUseNode);

            $alreadyUsedTraitNames[] = $traitName;
        }

        foreach (\array_diff($classReflection->getTraitNames(), $alreadyUsedTraitNames) as $traitName) {
            $classNode->addStmt(new TraitUse([new FullyQualified($traitName)]));
        }
    }

    private function addProperties(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        $defaultProperties = $classReflection->getDefaultProperties();

        foreach ($classReflection->getProperties() as $propertyReflection) {
            if ( ! $this->isPropertyDeclaredInClass($propertyReflection, $classReflection)) {
                continue;
            }

            $propertyNode = $this->builderFactory->property($propertyReflection->getName());

            $this->addPropertyModifiers($propertyNode, $propertyReflection);
            $this->addDocComment($propertyNode, $propertyReflection);

            if (\array_key_exists($propertyReflection->getName(), $defaultProperties)) {
                $propertyNode->setDefault($defaultProperties[$propertyReflection->getName()]);
            }

            $classNode->addStmt($propertyNode);
        }
    }

    private function isPropertyDeclaredInClass(CoreReflectionProperty $propertyReflection, CoreReflectionClass $classReflection) : bool
    {
        if ($propertyReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
            return false;
        }

        foreach ($classReflection->getTraits() as $trait) {
            if ($trait->hasProperty($propertyReflection->getName())) {
                return false;
            }
        }

        return true;
    }

    private function addPropertyModifiers(Property $propertyNode, CoreReflectionProperty $propertyReflection) : void
    {
        if ($propertyReflection->isStatic()) {
            $propertyNode->makeStatic();
        }

        if ($propertyReflection->isPublic()) {
            $propertyNode->makePublic();
        }

        if ($propertyReflection->isProtected()) {
            $propertyNode->makeProtected();
        }

        if ($propertyReflection->isPrivate()) {
            $propertyNode->makePrivate();
        }
    }

    private function addConstants(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        foreach ($classReflection->getReflectionConstants() as $constantReflection) {
            if ($constantReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
                continue;
            }

            $classConstantNode = new ClassConst(
                [new Const_($constantReflection->getName(), $this->constantValueNode($constantReflection))],
                $this->constantVisibilityFlags($constantReflection)
            );

            if (false !== $constantReflection->getDocComment()) {
                $classConstantNode->setDocComment(new Doc($constantReflection->getDocComment()));
            }

            $classNode->addStmt($classConstantNode);
        }
    }

    /**
     * A little hack so we don't have to copy the code in PhpParser\BuilderAbstract::normalizeValue()
     */
    private function constantValueNode(ReflectionClassConstant $constant) : ?Expr
    {
        return $this
            ->builderFactory
            ->property('')
            ->setDefault($constant->getValue())
            ->getNode()
            ->props[0]
            ->default;
    }

    private function constantVisibilityFlags(ReflectionClassConstant $constant) : int
    {
        if ($constant->isPrivate()) {
            return ClassNode::MODIFIER_PRIVATE;
        }

        if ($constant->isProtected()) {
            return ClassNode::MODIFIER_PROTECTED;
        }

        return ClassNode::MODIFIER_PUBLIC;
    }

    private function addMethods(Declaration $classNode, CoreReflectionClass $classReflection) : void
    {
        foreach ($classReflection->getMethods() as $methodReflection) {
            if ( ! $this->isMethodDeclaredInClass($methodReflection, $classReflection)) {
                continue;
            }

            $methodNode = $this->builderFactory->method($methodReflection->getName());

            $this->addMethodFlags($methodNode, $methodReflection);
            $this->addDocComment($methodNode, $methodReflection);
            $this->addParameters($methodNode, $methodReflection);

            $returnType = $methodReflection->getReturnType();

            if (null !== $methodReflection->getReturnType()) {
                $methodNode->setReturnType($this->formatType($returnType));
            }

            $classNode->addStmt($methodNode);
        }
    }

    private function isMethodDeclaredInClass(CoreReflectionMethod $methodReflection, CoreReflectionClass $classReflection) : bool
    {
        if ($methodReflection->getDeclaringClass()->getName() !== $classReflection->getName()) {
            return false;
        }

        if (\array_key_exists($methodReflection->getName(), $classReflection->getTraitAliases())) {
            return false;
        }

        foreach ($classReflection->getTraits() as $trait) {
            if ($trait->hasMethod($methodReflection->getName())) {
                return false;
            }
        }

        return true;
    }

    private function addMethodFlags(Method $methodNode, CoreReflectionMethod $methodReflection) : void
    {
        if ($methodReflection->isFinal()) {
            $methodNode->makeFinal();
        }

        if ($methodReflection->isAbstract()) {
            $methodNode->makeAbstract();
        }

        if ($methodReflection->isStatic()) {
            $methodNode->makeStatic();
        }

        if ($methodReflection->isPublic()) {
            $methodNode->makePublic();
        }

        if ($methodReflection->isProtected()) {
            $methodNode->makeProtected();
        }

        if ($methodReflection->isPrivate()) {
            $methodNode->makePrivate();
        }

        if ($methodReflection->returnsReference()) {
            $methodNode->makeReturnByRef();
        }
    }

    private function addParameters(Method $methodNode, CoreReflectionMethod $methodReflection) : void
    {
        foreach ($methodReflection->getParameters() as $parameterReflection) {
            $parameterNode = $this->builderFactory->param($parameterReflection->getName());

            $this->addParameterModifiers($parameterReflection, $parameterNode);

            if ($parameterReflection->isOptional() && ! $parameterReflection->isVariadic()) {
                $parameterNode->setDefault($this->parameterDefaultValue($parameterReflection, $methodReflection));
            }

            $methodNode->addParam($this->addParameterModifiers($parameterReflection, $parameterNode));
        }
    }

    private function addParameterModifiers(ReflectionParameter $parameterReflection, Param $parameterNode) : Param
    {
        if ($parameterReflection->isVariadic()) {
            $parameterNode->makeVariadic();
        }

        if ($parameterReflection->isPassedByReference()) {
            $parameterNode->makeByRef();
        }

        $parameterType = $parameterReflection->getType();

        if (null !== $parameterReflection->getType()) {
            $parameterNode->setType($this->formatType($parameterType));
        }

        return $parameterNode;
    }

    /**
     * @return mixed
     */
    private function parameterDefaultValue(
        ReflectionParameter $parameterReflection,
        CoreReflectionMethod $methodReflection
    ) {
        if ($methodReflection->getDeclaringClass()->isInternal()) {
            return null;
        }

        return $parameterReflection->getDefaultValue();
    }

    /**
     * @param CoreReflectionType $type
     * @return NodeAbstract
     */
    private function formatType(CoreReflectionType $type) : NodeAbstract
    {
        $name     = (string) $type;
        $nameNode = $type->isBuiltin() || \in_array($name, ['self', 'parent'], true) ? new Name($name) : new FullyQualified($name);
        return $type->allowsNull() ? new NullableType($nameNode) : $nameNode;
    }
}

?>

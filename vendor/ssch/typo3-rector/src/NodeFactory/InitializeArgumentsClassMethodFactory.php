<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\VerbosityLevel;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use RectorPrefix20220527\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220527\Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;
final class InitializeArgumentsClassMethodFactory
{
    /**
     * @var string
     */
    private const METHOD_NAME = 'initializeArguments';
    /**
     * @var string
     */
    private const MIXED = 'mixed';
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    private $paramTypeInferer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker
     */
    private $classLikeExistenceChecker;
    public function __construct(NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver, StaticTypeMapper $staticTypeMapper, ParamTypeInferer $paramTypeInferer, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider, ValueResolver $valueResolver, AstResolver $astResolver, ClassLikeExistenceChecker $classLikeExistenceChecker)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->paramTypeInferer = $paramTypeInferer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->valueResolver = $valueResolver;
        $this->astResolver = $astResolver;
        $this->classLikeExistenceChecker = $classLikeExistenceChecker;
    }
    public function decorateClass(Class_ $class) : void
    {
        $renderClassMethod = $class->getMethod('render');
        if (!$renderClassMethod instanceof ClassMethod) {
            return;
        }
        $newStmts = $this->createStmts($renderClassMethod, $class);
        $classMethod = $this->findOrCreateInitializeArgumentsClassMethod($class);
        $classMethod->stmts = \array_merge((array) $classMethod->stmts, $newStmts);
    }
    private function findOrCreateInitializeArgumentsClassMethod(Class_ $class) : ClassMethod
    {
        $classMethod = $class->getMethod(self::METHOD_NAME);
        if (null !== $classMethod) {
            return $classMethod;
        }
        $classMethod = $this->createNewClassMethod();
        if ($this->doesParentClassMethodExist($class, self::METHOD_NAME)) {
            // not in analyzed scope, nothing we can do
            $parentConstructStaticCall = new StaticCall(new Name('parent'), new Identifier(self::METHOD_NAME));
            $classMethod->stmts[] = new Expression($parentConstructStaticCall);
        }
        // empty line between methods
        $class->stmts[] = new Nop();
        $class->stmts[] = $classMethod;
        return $classMethod;
    }
    private function createNewClassMethod() : ClassMethod
    {
        $methodBuilder = new MethodBuilder(self::METHOD_NAME);
        $methodBuilder->makePublic();
        $methodBuilder->setReturnType('void');
        return $methodBuilder->getNode();
    }
    /**
     * @return Expression[]
     */
    private function createStmts(ClassMethod $renderMethod, Class_ $class) : array
    {
        $argumentsAlreadyDefinedInParentCall = $this->extractArgumentsFromParentClasses($class);
        $paramTagsByName = $this->getParamTagsByName($renderMethod);
        $stmts = [];
        foreach ($renderMethod->params as $param) {
            $paramName = $this->nodeNameResolver->getName($param->var);
            if (\in_array($paramName, $argumentsAlreadyDefinedInParentCall, \true)) {
                continue;
            }
            $paramTagValueNode = $paramTagsByName[$paramName] ?? null;
            $docString = $this->createTypeInString($paramTagValueNode, $param);
            $docString = $this->transformDocStringToClassConstantIfPossible($docString);
            $args = [$paramName, $docString, $this->getDescription($paramTagValueNode)];
            if ($param->default instanceof Expr) {
                $args[] = new ConstFetch(new Name('false'));
                $defaultValue = $this->valueResolver->getValue($param->default);
                if (null !== $defaultValue && 'null' !== $defaultValue) {
                    $args[] = $defaultValue;
                }
            } else {
                $args[] = new ConstFetch(new Name('true'));
            }
            $methodCall = $this->nodeFactory->createMethodCall('this', 'registerArgument', $args);
            $stmts[] = new Expression($methodCall);
        }
        return $stmts;
    }
    /**
     * @return array<string, ParamTagValueNode>
     */
    private function getParamTagsByName(ClassMethod $classMethod) : array
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return [];
        }
        $paramTagsByName = [];
        foreach ($phpDocInfo->getTagsByName('param') as $phpDocTagNode) {
            if (\property_exists($phpDocTagNode, 'value')) {
                /** @var ParamTagValueNode $paramTagValueNode */
                $paramTagValueNode = $phpDocTagNode->value;
                if (\is_string($paramTagValueNode->parameterName)) {
                    $paramName = \ltrim($paramTagValueNode->parameterName, '$');
                    $paramTagsByName[$paramName] = $paramTagValueNode;
                }
            }
        }
        return $paramTagsByName;
    }
    private function getDescription(?ParamTagValueNode $paramTagValueNode) : string
    {
        return $paramTagValueNode instanceof ParamTagValueNode ? $paramTagValueNode->description : '';
    }
    private function createTypeInString(?ParamTagValueNode $paramTagValueNode, Param $param) : string
    {
        if (null !== $param->type) {
            return $this->resolveParamType($param->type);
        }
        if (null !== $paramTagValueNode && $paramTagValueNode->type instanceof IdentifierTypeNode) {
            return $paramTagValueNode->type->name;
        }
        $inferredType = $this->paramTypeInferer->inferParam($param);
        if ($inferredType instanceof MixedType) {
            return self::MIXED;
        }
        if ($this->isTraitType($inferredType)) {
            return self::MIXED;
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferredType, TypeKind::PARAM());
        if ($paramTypeNode instanceof UnionType) {
            return self::MIXED;
        }
        if ($paramTypeNode instanceof NullableType) {
            return self::MIXED;
        }
        if ($paramTypeNode instanceof Name) {
            return $paramTypeNode->__toString();
        }
        if (null === $paramTagValueNode) {
            return self::MIXED;
        }
        $phpStanType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($paramTagValueNode->type, $param);
        $docString = $phpStanType->describe(VerbosityLevel::typeOnly());
        if (\substr_compare($docString, '[]', -\strlen('[]')) === 0) {
            return 'array';
        }
        return $docString;
    }
    private function isTraitType(Type $type) : bool
    {
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        $fullyQualifiedName = $this->getFullyQualifiedName($type);
        if (!$this->reflectionProvider->hasClass($fullyQualifiedName)) {
            return \false;
        }
        $reflectionClass = $this->reflectionProvider->getClass($fullyQualifiedName);
        return $reflectionClass->isTrait();
    }
    private function getFullyQualifiedName(TypeWithClassName $typeWithClassName) : string
    {
        if ($typeWithClassName instanceof ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        return $typeWithClassName->getClassName();
    }
    private function resolveParamType(Node $paramType) : string
    {
        if ($paramType instanceof FullyQualified) {
            return $paramType->toCodeString();
        }
        return $this->nodeNameResolver->getName($paramType) ?? self::MIXED;
    }
    /**
     * @return MethodReflection[]
     */
    private function getParentClassesMethodReflection(Class_ $class, string $methodName) : array
    {
        $scope = $class->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return [];
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return [];
        }
        $parentMethods = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                $parentMethods[] = $parentClassReflection->getMethod($methodName, $scope);
            }
        }
        return $parentMethods;
    }
    private function doesParentClassMethodExist(Class_ $class, string $methodName) : bool
    {
        return [] !== $this->getParentClassesMethodReflection($class, $methodName);
    }
    /**
     * @return array<int, string>
     */
    private function extractArgumentsFromParentClasses(Class_ $class) : array
    {
        $definedArguments = [];
        $methodReflections = $this->getParentClassesMethodReflection($class, self::METHOD_NAME);
        foreach ($methodReflections as $methodReflection) {
            $classMethod = $this->astResolver->resolveClassMethodFromMethodReflection($methodReflection);
            if (!$classMethod instanceof ClassMethod) {
                continue;
            }
            if (null === $classMethod->stmts) {
                continue;
            }
            foreach ($classMethod->stmts as $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }
                if (!$stmt->expr instanceof MethodCall) {
                    continue;
                }
                if (!$this->nodeNameResolver->isName($stmt->expr->name, 'registerArgument')) {
                    continue;
                }
                $value = $this->valueResolver->getValue($stmt->expr->args[0]->value);
                if (null === $value) {
                    continue;
                }
                $definedArguments[] = $value;
            }
        }
        return $definedArguments;
    }
    /**
     * @return \PhpParser\Node\Expr\ClassConstFetch|string
     */
    private function transformDocStringToClassConstantIfPossible(string $docString)
    {
        // remove leading slash
        $classLikeName = \ltrim($docString, '\\');
        if ('' === $classLikeName) {
            return $docString;
        }
        if (!$this->classLikeExistenceChecker->doesClassLikeExist($classLikeName)) {
            return $classLikeName;
        }
        $fullyQualified = new FullyQualified($classLikeName);
        return new ClassConstFetch($fullyQualified, 'class');
    }
}

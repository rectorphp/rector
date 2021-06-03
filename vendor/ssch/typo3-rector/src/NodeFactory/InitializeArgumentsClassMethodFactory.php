<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
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
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\VerbosityLevel;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use RectorPrefix20210603\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
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
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    private $paramTypeInferer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer $paramTypeInferer, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->paramTypeInferer = $paramTypeInferer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function decorateClass(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $renderClassMethod = $class->getMethod('render');
        if (null === $renderClassMethod) {
            return;
        }
        $newStmts = $this->createStmts($renderClassMethod);
        $classMethod = $this->findOrCreateInitializeArgumentsClassMethod($class);
        $classMethod->stmts = \array_merge((array) $classMethod->stmts, $newStmts);
    }
    private function findOrCreateInitializeArgumentsClassMethod(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt\ClassMethod
    {
        $classMethod = $class->getMethod(self::METHOD_NAME);
        if (null !== $classMethod) {
            return $classMethod;
        }
        $classMethod = $this->createNewClassMethod();
        if ($this->doesParentClassMethodExist($class, self::METHOD_NAME)) {
            // not in analyzed scope, nothing we can do
            $parentConstructCallNode = new \PhpParser\Node\Expr\StaticCall(new \PhpParser\Node\Name('parent'), new \PhpParser\Node\Identifier(self::METHOD_NAME));
            $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($parentConstructCallNode);
        }
        // empty line between methods
        $class->stmts[] = new \PhpParser\Node\Stmt\Nop();
        $class->stmts[] = $classMethod;
        return $classMethod;
    }
    private function createNewClassMethod() : \PhpParser\Node\Stmt\ClassMethod
    {
        $methodBuilder = new \RectorPrefix20210603\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder(self::METHOD_NAME);
        $methodBuilder->makePublic();
        $methodBuilder->setReturnType('void');
        return $methodBuilder->getNode();
    }
    /**
     * @return Expression[]
     */
    private function createStmts(\PhpParser\Node\Stmt\ClassMethod $renderMethod) : array
    {
        $paramTagsByName = $this->getParamTagsByName($renderMethod);
        $stmts = [];
        foreach ($renderMethod->params as $param) {
            $paramName = $this->nodeNameResolver->getName($param->var);
            $paramTagValueNode = $paramTagsByName[$paramName] ?? null;
            $docString = $this->createTypeInString($paramTagValueNode, $param);
            $args = [$paramName, $docString, $this->getDescription($paramTagValueNode)];
            if ($param->default instanceof \PhpParser\Node\Expr) {
                $args[] = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('false'));
                if (\property_exists($param->default, 'value')) {
                    $args[] = $param->default->value;
                }
            } else {
                $args[] = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('true'));
            }
            $methodCall = $this->nodeFactory->createMethodCall('this', 'registerArgument', $args);
            $stmts[] = new \PhpParser\Node\Stmt\Expression($methodCall);
        }
        return $stmts;
    }
    /**
     * @return array<string, ParamTagValueNode>
     */
    private function getParamTagsByName(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return [];
        }
        $paramTagsByName = [];
        foreach ($phpDocInfo->getTagsByName('param') as $phpDocTagNode) {
            if (\property_exists($phpDocTagNode, 'value')) {
                /** @var ParamTagValueNode $paramTagValueNode */
                $paramTagValueNode = $phpDocTagNode->value;
                $paramName = \ltrim($paramTagValueNode->parameterName, '$');
                $paramTagsByName[$paramName] = $paramTagValueNode;
            }
        }
        return $paramTagsByName;
    }
    private function getDescription(?\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode $paramTagValueNode) : string
    {
        return $paramTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode ? $paramTagValueNode->description : '';
    }
    private function createTypeInString(?\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode $paramTagValueNode, \PhpParser\Node\Param $param) : string
    {
        if (null !== $param->type) {
            return $this->resolveParamType($param->type);
        }
        if (null !== $paramTagValueNode && $paramTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
            return $paramTagValueNode->type->name;
        }
        $inferedType = $this->paramTypeInferer->inferParam($param);
        if ($inferedType instanceof \PHPStan\Type\MixedType) {
            return self::MIXED;
        }
        if ($this->isTraitType($inferedType)) {
            return self::MIXED;
        }
        $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::KIND_PARAM);
        if ($paramTypeNode instanceof \PhpParser\Node\UnionType) {
            return self::MIXED;
        }
        if ($paramTypeNode instanceof \PhpParser\Node\NullableType) {
            return self::MIXED;
        }
        if (null !== $paramTypeNode) {
            return $paramTypeNode->toString();
        }
        if (null === $paramTagValueNode) {
            return self::MIXED;
        }
        $phpStanType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($paramTagValueNode->type, $param);
        $docString = $phpStanType->describe(\PHPStan\Type\VerbosityLevel::typeOnly());
        if ('[]' === \substr($docString, -2)) {
            return 'array';
        }
        return $docString;
    }
    private function isTraitType(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\TypeWithClassName) {
            return \false;
        }
        $fullyQualifiedName = $this->getFullyQualifiedName($type);
        if (!$this->reflectionProvider->hasClass($fullyQualifiedName)) {
            return \false;
        }
        $reflectionClass = $this->reflectionProvider->getClass($fullyQualifiedName);
        return $reflectionClass->isTrait();
    }
    private function getFullyQualifiedName(\PHPStan\Type\TypeWithClassName $typeWithClassName) : string
    {
        if ($typeWithClassName instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            return $typeWithClassName->getFullyQualifiedName();
        }
        return $typeWithClassName->getClassName();
    }
    private function resolveParamType(\PhpParser\Node $paramType) : string
    {
        if ($paramType instanceof \PhpParser\Node\Name\FullyQualified) {
            return $paramType->toCodeString();
        }
        return $this->nodeNameResolver->getName($paramType) ?? self::MIXED;
    }
    private function doesParentClassMethodExist(\PhpParser\Node\Stmt\Class_ $class, string $methodName) : bool
    {
        $scope = $class->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (null === $classReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
}

<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PhpParser\NodeVisitor;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Naming\PropertyRenamer\PropertyPromotionRenamer;
use Rector\Naming\VariableRenamer;
use Rector\NodeAnalyzer\ParamAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\Php80\DocBlock\PropertyPromotionDocBlockMerger;
use Rector\Php80\Guard\MakePropertyPromotionGuard;
use Rector\Php80\NodeAnalyzer\PromotedPropertyCandidateResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\ClassPropertyAssignToConstructorPromotionRectorTest
 */
final class ClassPropertyAssignToConstructorPromotionRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private PromotedPropertyCandidateResolver $promotedPropertyCandidateResolver;
    /**
     * @readonly
     */
    private VariableRenamer $variableRenamer;
    /**
     * @readonly
     */
    private ParamAnalyzer $paramAnalyzer;
    /**
     * @readonly
     */
    private PropertyPromotionDocBlockMerger $propertyPromotionDocBlockMerger;
    /**
     * @readonly
     */
    private MakePropertyPromotionGuard $makePropertyPromotionGuard;
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private PropertyPromotionRenamer $propertyPromotionRenamer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @api
     * @var string
     */
    public const INLINE_PUBLIC = 'inline_public';
    /**
     * @api
     * @var string
     */
    public const RENAME_PROPERTY = 'rename_property';
    /**
     * Default to false, which only apply changes:
     *
     *  – private modifier property
     *  - protected/public modifier property when property typed
     *
     * Set to true will allow change whether property is typed or not as far as not forbidden, eg: callable type, null type, etc.
     */
    private bool $inlinePublic = \false;
    /**
     * Set to false will skip property promotion when parameter and property have different names.
     */
    private bool $renameProperty = \true;
    public function __construct(PromotedPropertyCandidateResolver $promotedPropertyCandidateResolver, VariableRenamer $variableRenamer, ParamAnalyzer $paramAnalyzer, PropertyPromotionDocBlockMerger $propertyPromotionDocBlockMerger, MakePropertyPromotionGuard $makePropertyPromotionGuard, TypeComparator $typeComparator, ReflectionResolver $reflectionResolver, PropertyPromotionRenamer $propertyPromotionRenamer, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper)
    {
        $this->promotedPropertyCandidateResolver = $promotedPropertyCandidateResolver;
        $this->variableRenamer = $variableRenamer;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->propertyPromotionDocBlockMerger = $propertyPromotionDocBlockMerger;
        $this->makePropertyPromotionGuard = $makePropertyPromotionGuard;
        $this->typeComparator = $typeComparator;
        $this->reflectionResolver = $reflectionResolver;
        $this->propertyPromotionRenamer = $propertyPromotionRenamer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change simple property init and assign to constructor promotion', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public float $price;

    public function __construct(
        float $price = 0.0
    ) {
        $this->price = $price;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        public float $price = 0.0
    ) {
    }
}
CODE_SAMPLE
, [self::INLINE_PUBLIC => \false, self::RENAME_PROPERTY => \true])]);
    }
    public function configure(array $configuration) : void
    {
        $this->inlinePublic = $configuration[self::INLINE_PUBLIC] ?? \false;
        $this->renameProperty = $configuration[self::RENAME_PROPERTY] ?? \true;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        $promotionCandidates = $this->promotedPropertyCandidateResolver->resolveFromClass($node, $constructClassMethod);
        if ($promotionCandidates === []) {
            return null;
        }
        $constructorPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($constructClassMethod);
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($promotionCandidates as $promotionCandidate) {
            $param = $promotionCandidate->getParam();
            if ($this->shouldSkipParam($param)) {
                continue;
            }
            $property = $promotionCandidate->getProperty();
            if (!$this->makePropertyPromotionGuard->isLegal($node, $classReflection, $property, $param, $this->inlinePublic)) {
                continue;
            }
            $paramName = $this->getName($param);
            // rename also following calls
            $propertyName = $this->getName($property->props[0]);
            if (!$this->renameProperty && $paramName !== $propertyName) {
                continue;
            }
            $hasChanged = \true;
            // remove property from class
            $propertyStmtKey = $property->getAttribute(AttributeKey::STMT_KEY);
            unset($node->stmts[$propertyStmtKey]);
            // remove assign in constructor
            $assignStmtPosition = $promotionCandidate->getStmtPosition();
            unset($constructClassMethod->stmts[$assignStmtPosition]);
            /** @var string $oldName */
            $oldName = $this->getName($param->var);
            $this->variableRenamer->renameVariableInFunctionLike($constructClassMethod, $oldName, $propertyName, null);
            $paramTagValueNode = $constructorPhpDocInfo->getParamTagValueByName($paramName);
            if (!$paramTagValueNode instanceof ParamTagValueNode) {
                $this->propertyPromotionDocBlockMerger->decorateParamWithPropertyPhpDocInfo($constructClassMethod, $property, $param, $paramName);
            } elseif ($paramTagValueNode->parameterName !== '$' . $propertyName) {
                $this->propertyPromotionRenamer->renameParamDoc($constructorPhpDocInfo, $constructClassMethod, $param, $paramTagValueNode->parameterName, $propertyName);
            }
            // property name has higher priority
            $paramName = $this->getName($property);
            $param->var = new Variable($paramName);
            $param->flags = $property->flags;
            // copy attributes of the old property
            $param->attrGroups = \array_merge($param->attrGroups, $property->attrGroups);
            $this->processUnionType($property, $param);
            $this->propertyPromotionDocBlockMerger->mergePropertyAndParamDocBlocks($property, $param, $paramTagValueNode);
            // update variable to property fetch references
            $this->traverseNodesWithCallable((array) $constructClassMethod->stmts, function (Node $node) use($promotionCandidate, $propertyName) {
                if ($node instanceof Class_ || $node instanceof FunctionLike) {
                    return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                if (!$node instanceof Variable) {
                    return null;
                }
                if (!$this->isName($node, $promotionCandidate->getParamName())) {
                    return null;
                }
                return new PropertyFetch(new Variable('this'), $propertyName);
            });
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::PROPERTY_PROMOTION;
    }
    private function processUnionType(Property $property, Param $param) : void
    {
        if ($property->type instanceof Node) {
            $param->type = $property->type;
            return;
        }
        if (!$param->default instanceof Expr) {
            return;
        }
        if (!$param->type instanceof Node) {
            return;
        }
        $defaultType = $this->getType($param->default);
        $paramType = $this->getType($param->type);
        if ($this->typeComparator->isSubtype($defaultType, $paramType)) {
            return;
        }
        if ($this->typeComparator->areTypesEqual($defaultType, $paramType)) {
            return;
        }
        if ($paramType instanceof MixedType) {
            return;
        }
        $paramType = TypeCombinator::union($paramType, $defaultType);
        $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType, TypeKind::PARAM);
    }
    private function shouldSkipParam(Param $param) : bool
    {
        if ($param->variadic) {
            return \true;
        }
        if ($this->paramAnalyzer->isNullable($param)) {
            /** @var NullableType $type */
            $type = $param->type;
            $type = $type->type;
        } else {
            $type = $param->type;
        }
        if ($this->isCallableTypeIdentifier($type)) {
            return \true;
        }
        if (!$type instanceof UnionType) {
            return \false;
        }
        foreach ($type->types as $type) {
            if ($this->isCallableTypeIdentifier($type)) {
                return \true;
            }
        }
        return \false;
    }
    private function isCallableTypeIdentifier(?Node $node) : bool
    {
        return $node instanceof Identifier && $this->isName($node, 'callable');
    }
}

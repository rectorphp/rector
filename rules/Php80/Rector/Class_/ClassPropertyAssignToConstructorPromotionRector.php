<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\Naming\VariableRenamer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\Php80\Guard\MakePropertyPromotionGuard;
use Rector\Php80\NodeAnalyzer\PromotedPropertyCandidateResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/constructor_promotion https://github.com/php/php-src/pull/5291
 *
 * @see \Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\ClassPropertyAssignToConstructorPromotionRectorTest
 */
final class ClassPropertyAssignToConstructorPromotionRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyCandidateResolver
     */
    private $promotedPropertyCandidateResolver;
    /**
     * @readonly
     * @var \Rector\Naming\VariableRenamer
     */
    private $variableRenamer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Php80\Guard\MakePropertyPromotionGuard
     */
    private $makePropertyPromotionGuard;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @api
     * @var string
     */
    public const INLINE_PUBLIC = 'inline_public';
    /**
     * Default to false, which only apply changes:
     *
     *  – private modifier property
     *  - protected/public modifier property when property typed
     *
     * Set to true will allow change whether property is typed or not as far as not forbidden, eg: callable type, null type, etc.
     * @var bool
     */
    private $inlinePublic = \false;
    public function __construct(PromotedPropertyCandidateResolver $promotedPropertyCandidateResolver, VariableRenamer $variableRenamer, VarTagRemover $varTagRemover, ParamAnalyzer $paramAnalyzer, PhpDocTypeChanger $phpDocTypeChanger, MakePropertyPromotionGuard $makePropertyPromotionGuard, TypeComparator $typeComparator, ReflectionResolver $reflectionResolver)
    {
        $this->promotedPropertyCandidateResolver = $promotedPropertyCandidateResolver;
        $this->variableRenamer = $variableRenamer;
        $this->varTagRemover = $varTagRemover;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->makePropertyPromotionGuard = $makePropertyPromotionGuard;
        $this->typeComparator = $typeComparator;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change simple property init and assign to constructor promotion', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public float $someVariable;

    public function __construct(
        float $someVariable = 0.0
    ) {
        $this->someVariable = $someVariable;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        public float $someVariable = 0.0
    ) {
    }
}
CODE_SAMPLE
, [self::INLINE_PUBLIC => \false])]);
    }
    public function configure(array $configuration) : void
    {
        $this->inlinePublic = $configuration[self::INLINE_PUBLIC] ?? (bool) \current($configuration);
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
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($constructClassMethod);
        $classReflection = null;
        foreach ($promotionCandidates as $promotionCandidate) {
            // does property have some useful annotations?
            $property = $promotionCandidate->getProperty();
            $param = $promotionCandidate->getParam();
            if ($this->shouldSkipParam($param)) {
                continue;
            }
            if (!$classReflection instanceof ClassReflection) {
                $classReflection = $this->reflectionResolver->resolveClassReflection($node);
            }
            if (!$classReflection instanceof ClassReflection) {
                return null;
            }
            if (!$this->makePropertyPromotionGuard->isLegal($node, $classReflection, $property, $param, $this->inlinePublic)) {
                continue;
            }
            $propertyStmtKey = $property->getAttribute(AttributeKey::STMT_KEY);
            unset($node->stmts[$propertyStmtKey]);
            // remove assign
            $assignStmtPosition = $promotionCandidate->getStmtPosition();
            unset($constructClassMethod->stmts[$assignStmtPosition]);
            $property = $promotionCandidate->getProperty();
            $paramName = $this->getName($param);
            // rename also following calls
            $propertyName = $this->getName($property->props[0]);
            /** @var string $oldName */
            $oldName = $this->getName($param->var);
            $this->variableRenamer->renameVariableInFunctionLike($constructClassMethod, $oldName, $propertyName, null);
            $paramTagValueNode = $classMethodPhpDocInfo->getParamTagValueByName($paramName);
            if (!$paramTagValueNode instanceof ParamTagValueNode) {
                $this->decorateParamWithPropertyPhpDocInfo($constructClassMethod, $property, $param, $paramName);
            } elseif ($paramTagValueNode->parameterName !== '$' . $propertyName) {
                $paramTagValueNode->parameterName = '$' . $propertyName;
                $paramTagValueNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
            }
            // property name has higher priority
            $paramName = $this->getName($property);
            $param->var = new Variable($paramName);
            $param->flags = $property->flags;
            // Copy over attributes of the "old" property
            $param->attrGroups = \array_merge($param->attrGroups, $property->attrGroups);
            $this->processUnionType($property, $param);
            $this->phpDocTypeChanger->copyPropertyDocToParam($constructClassMethod, $property, $param);
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
    private function decorateParamWithPropertyPhpDocInfo(ClassMethod $classMethod, Property $property, Param $param, string $paramName) : void
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $propertyPhpDocInfo->markAsChanged();
        $param->setAttribute(AttributeKey::PHP_DOC_INFO, $propertyPhpDocInfo);
        // make sure the docblock is useful
        if ($param->type === null) {
            $varTagValueNode = $propertyPhpDocInfo->getVarTagValueNode();
            if (!$varTagValueNode instanceof VarTagValueNode) {
                return;
            }
            $paramType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($varTagValueNode, $property);
            $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $this->phpDocTypeChanger->changeParamType($classMethod, $classMethodPhpDocInfo, $paramType, $param, $paramName);
        } else {
            $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        }
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($param, $paramType);
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

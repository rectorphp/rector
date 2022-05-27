<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\NodeAnalyzer\PropertyAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\Naming\VariableRenamer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeAnalyzer\PromotedPropertyCandidateResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/constructor_promotion https://github.com/php/php-src/pull/5291
 *
 * @see \Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\ClassPropertyAssignToConstructorPromotionRectorTest
 */
final class ClassPropertyAssignToConstructorPromotionRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
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
     * @var \Rector\Core\NodeAnalyzer\PropertyAnalyzer
     */
    private $propertyAnalyzer;
    public function __construct(\Rector\Php80\NodeAnalyzer\PromotedPropertyCandidateResolver $promotedPropertyCandidateResolver, \Rector\Naming\VariableRenamer $variableRenamer, \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover $varTagRemover, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Core\NodeAnalyzer\PropertyAnalyzer $propertyAnalyzer)
    {
        $this->promotedPropertyCandidateResolver = $promotedPropertyCandidateResolver;
        $this->variableRenamer = $variableRenamer;
        $this->varTagRemover = $varTagRemover;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->propertyAnalyzer = $propertyAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change simple property init and assign to constructor promotion', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        private float $someVariable = 0.0
    ) {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $promotionCandidates = $this->promotedPropertyCandidateResolver->resolveFromClass($node);
        if ($promotionCandidates === []) {
            return null;
        }
        /** @var ClassMethod $constructClassMethod */
        $constructClassMethod = $node->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($constructClassMethod);
        foreach ($promotionCandidates as $promotionCandidate) {
            // does property have some useful annotations?
            $property = $promotionCandidate->getProperty();
            $param = $promotionCandidate->getParam();
            if ($this->shouldSkipParam($param)) {
                continue;
            }
            if ($this->propertyAnalyzer->hasForbiddenType($property)) {
                continue;
            }
            $this->removeNode($property);
            $this->removeNode($promotionCandidate->getAssign());
            $property = $promotionCandidate->getProperty();
            $paramName = $this->getName($param);
            // rename also following calls
            $propertyName = $this->getName($property->props[0]);
            /** @var string $oldName */
            $oldName = $this->getName($param->var);
            $this->variableRenamer->renameVariableInFunctionLike($constructClassMethod, $oldName, $propertyName, null);
            $paramTagValueNode = $classMethodPhpDocInfo->getParamTagValueNodeByName($paramName);
            if (!$paramTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode) {
                $this->decorateParamWithPropertyPhpDocInfo($property, $param);
            } elseif ($paramTagValueNode->parameterName !== '$' . $propertyName) {
                $paramTagValueNode->parameterName = '$' . $propertyName;
                $paramTagValueNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE, null);
            }
            // property name has higher priority
            $propertyName = $this->getName($property);
            $param->var->name = $propertyName;
            $param->flags = $property->flags;
            // Copy over attributes of the "old" property
            $param->attrGroups = $property->attrGroups;
            $this->processNullableType($property, $param);
            $this->phpDocTypeChanger->copyPropertyDocToParam($property, $param);
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::PROPERTY_PROMOTION;
    }
    private function processNullableType(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Param $param) : void
    {
        if ($this->nodeTypeResolver->isNullableType($property)) {
            $objectType = $this->getType($property);
            $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($objectType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
        }
    }
    private function decorateParamWithPropertyPhpDocInfo(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Param $param) : void
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $propertyPhpDocInfo->markAsChanged();
        $param->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $propertyPhpDocInfo);
        // make sure the docblock is useful
        if ($param->type === null) {
            return;
        }
        $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $this->varTagRemover->removeVarPhpTagValueNodeIfNotComment($param, $paramType);
    }
    private function shouldSkipParam(\PhpParser\Node\Param $param) : bool
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
        if (!$type instanceof \PhpParser\Node\UnionType) {
            return \false;
        }
        foreach ($type->types as $type) {
            if (!$type instanceof \PhpParser\Node\Identifier) {
                continue;
            }
            if (!$this->isName($type, 'callable')) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}

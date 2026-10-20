<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\StaticTypeMapper\StaticTypeMapper;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector\AddParamTypeSplFixedArrayRectorTest
 */
final class AddParamTypeSplFixedArrayRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @var array<string, string>
     */
    private const SPL_FIXED_ARRAY_TO_SINGLE = ['PhpCsFixer\Tokenizer\Tokens' => 'PhpCsFixer\Tokenizer\Token', 'PhpCsFixer\Doctrine\Annotation\Tokens' => 'PhpCsFixer\Doctrine\Annotation\Token'];
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add exact fixed array type in known cases', [new CodeSample(<<<'CODE_SAMPLE'
use PhpCsFixer\Tokenizer\Tokens;

class SomeClass
{
    public function run(Tokens $tokens)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

class SomeClass
{
    /**
     * @param Tokens<Token>
     */
    public function run(Tokens $tokens)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getParams() === []) {
            return null;
        }
        $functionLikePhpDocInfo = null;
        $hasChanged = \false;
        foreach ($node->getParams() as $param) {
            if ($param->type === null) {
                continue;
            }
            $paramType = $this->getType($param->type);
            if ($paramType->isSuperTypeOf(new ObjectType('SplFixedArray'))->no()) {
                continue;
            }
            if (!$paramType instanceof TypeWithClassName) {
                continue;
            }
            if ($paramType instanceof GenericObjectType) {
                continue;
            }
            $genericParamType = $this->resolveGenericType($paramType);
            if (!$genericParamType instanceof Type) {
                continue;
            }
            $functionLikePhpDocInfo ??= $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            $paramName = $this->getName($param);
            if ($this->isAlreadyDocumented($functionLikePhpDocInfo, $param, $paramName, $genericParamType)) {
                continue;
            }
            $changedParamType = $this->phpDocTypeChanger->changeParamType($node, $functionLikePhpDocInfo, $genericParamType, $param, $paramName);
            if ($changedParamType) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * An existing "Tokens<Token>" docblock, with short or fully qualified names, resolves to "iterable<Token>&Tokens",
     * as the fixed array class is not generic itself. That never equals the generic object type, so the tag would be
     * rewritten with fully qualified names on every run.
     */
    private function isAlreadyDocumented(PhpDocInfo $phpDocInfo, Param $param, string $paramName, GenericObjectType $genericObjectType): bool
    {
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
        if (!$paramTagValueNode instanceof ParamTagValueNode) {
            return \false;
        }
        $genericTypeNode = $paramTagValueNode->type;
        if (!$genericTypeNode instanceof GenericTypeNode || count($genericTypeNode->genericTypes) !== 1) {
            return \false;
        }
        $currentType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($genericTypeNode->type, $param);
        $currentItemType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($genericTypeNode->genericTypes[0], $param);
        return $currentType->equals(new ObjectType($genericObjectType->getClassName())) && $currentItemType->equals($genericObjectType->getTypes()[0]);
    }
    private function resolveGenericType(TypeWithClassName $typeWithClassName): ?\PHPStan\Type\Generic\GenericObjectType
    {
        foreach (self::SPL_FIXED_ARRAY_TO_SINGLE as $fixedArrayClass => $singleClass) {
            if ($typeWithClassName->getClassName() === $fixedArrayClass) {
                $genericObjectType = new ObjectType($singleClass);
                return new GenericObjectType($typeWithClassName->getClassName(), [$genericObjectType]);
            }
        }
        return null;
    }
}

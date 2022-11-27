<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector\AddParamTypeSplFixedArrayRectorTest
 */
final class AddParamTypeSplFixedArrayRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const SPL_FIXED_ARRAY_TO_SINGLE = ['PhpCsFixer\\Tokenizer\\Tokens' => 'PhpCsFixer\\Tokenizer\\Token', 'PhpCsFixer\\Doctrine\\Annotation\\Tokens' => 'PhpCsFixer\\Doctrine\\Annotation\\Token'];
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Function_::class, ClassMethod::class];
    }
    public function getRuleDefinition() : RuleDefinition
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
    public function refactor(Node $node) : ?Node
    {
        if ($node->getParams() === []) {
            return null;
        }
        $functionLikePhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($node->getParams() as $param) {
            if ($param->type === null) {
                continue;
            }
            $paramType = $this->nodeTypeResolver->getType($param->type);
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
            $paramName = $this->getName($param);
            $this->phpDocTypeChanger->changeParamType($functionLikePhpDocInfo, $genericParamType, $param, $paramName);
        }
        if ($functionLikePhpDocInfo->hasChanged()) {
            return $node;
        }
        return null;
    }
    private function resolveGenericType(TypeWithClassName $typeWithClassName) : ?\PHPStan\Type\Generic\GenericObjectType
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

<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\NeverType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220418\Symplify\Astral\TypeAnalyzer\ClassMethodReturnTypeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\ArrayShapeFromConstantArrayReturnRectorTest
 */
final class ArrayShapeFromConstantArrayReturnRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @see https://regex101.com/r/WvUD0m/2
     * @var string
     */
    private const SKIPPED_CHAR_REGEX = '#\\W#u';
    /**
     * @readonly
     * @var \Symplify\Astral\TypeAnalyzer\ClassMethodReturnTypeResolver
     */
    private $classMethodReturnTypeResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(\RectorPrefix20220418\Symplify\Astral\TypeAnalyzer\ClassMethodReturnTypeResolver $classMethodReturnTypeResolver, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->classMethodReturnTypeResolver = $classMethodReturnTypeResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add array shape exact types based on constant keys of array', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(string $name)
    {
        return ['name' => $name];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return array{name: string}
     */
    public function run(string $name)
    {
        return ['name' => $name];
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($node, \PhpParser\Node\Stmt\Return_::class);
        // exact one shape only
        if (\count($returns) !== 1) {
            return null;
        }
        $return = $returns[0];
        if (!$return->expr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $returnExprType = $this->getType($return->expr);
        if (!$returnExprType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return null;
        }
        if ($this->shouldSkip($returnExprType)) {
            return null;
        }
        $returnType = $this->classMethodReturnTypeResolver->resolve($node, $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE));
        if ($returnType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnExprTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($returnExprType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
        if ($returnExprTypeNode instanceof \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode) {
            return null;
        }
        if ($returnExprTypeNode instanceof \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode) {
            return null;
        }
        $hasChanged = $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $returnExprType);
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function shouldSkip(\PHPStan\Type\Constant\ConstantArrayType $constantArrayType) : bool
    {
        $keyType = $constantArrayType->getKeyType();
        // empty array
        if ($keyType instanceof \PHPStan\Type\NeverType) {
            return \true;
        }
        $types = $keyType instanceof \PHPStan\Type\UnionType ? $keyType->getTypes() : [$keyType];
        foreach ($types as $type) {
            if (!$type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                continue;
            }
            $value = $type->getValue();
            if (\trim($value) === '') {
                return \true;
            }
            if (\Rector\Core\Util\StringUtils::isMatch($value, self::SKIPPED_CHAR_REGEX)) {
                return \true;
            }
        }
        $itemType = $constantArrayType->getItemType();
        if ($itemType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return $this->shouldSkip($itemType);
        }
        return \false;
    }
}

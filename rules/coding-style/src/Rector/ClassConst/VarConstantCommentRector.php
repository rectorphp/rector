<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\VarConstantCommentRectorTest
 */
final class VarConstantCommentRector extends AbstractRector
{
    /**
     * @var TypeComparator
     */
    private $typeComparator;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(TypeComparator $typeComparator, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->typeComparator = $typeComparator;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Constant should have a @var comment with type',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    const HI = 'hi';
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string
     */
    const HI = 'hi';
}
CODE_SAMPLE
            ),
            ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->consts) > 1) {
            return null;
        }

        $constType = $this->getStaticType($node->consts[0]->value);
        if ($constType instanceof MixedType) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        // skip big arrays and mixed[] constants
        if ($constType instanceof ConstantArrayType) {
            $currentVarType = $phpDocInfo->getVarType();
            if ($currentVarType instanceof ArrayType && $currentVarType->getItemType() instanceof MixedType) {
                return null;
            }

            if ($this->hasTwoAndMoreGenericClassStringTypes($constType)) {
                return null;
            }
        }

        if ($this->typeComparator->isSubtype($constType, $phpDocInfo->getVarType())) {
            return null;
        }

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $constType);

        return $node;
    }

    private function hasTwoAndMoreGenericClassStringTypes(ConstantArrayType $constantArrayType): bool
    {
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($constantArrayType);
        if (! $typeNode instanceof ArrayTypeNode) {
            return false;
        }

        if (! $typeNode->type instanceof UnionTypeNode) {
            return false;
        }

        $genericTypeNodeCount = 0;
        foreach ($typeNode->type->types as $unionedTypeNode) {
            if ($unionedTypeNode instanceof GenericTypeNode) {
                ++$genericTypeNodeCount;
            }
        }

        return $genericTypeNodeCount > 1;
    }
}

<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\VarConstantCommentRectorTest
 */
final class VarConstantCommentRector extends AbstractRector
{
    /**
     * @var int
     */
    private const ARRAY_LIMIT_TYPES = 3;

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
     * @return string[]
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

        $phpDocInfo = $this->getOrCreatePhpDocInfo($node);

        // skip big arrays and mixed[] constants
        if ($constType instanceof ConstantArrayType) {
            if (count($constType->getValueTypes()) > self::ARRAY_LIMIT_TYPES) {
                return null;
            }

            $currentVarType = $phpDocInfo->getVarType();
            if ($currentVarType instanceof ArrayType && $currentVarType->getItemType() instanceof MixedType) {
                return null;
            }
        }

        if ($phpDocInfo instanceof PhpDocInfo && $this->typeComparator->isSubtype(
            $constType,
            $phpDocInfo->getVarType()
        )) {
            return null;
        }

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $constType);

        return $node;
    }

    private function getOrCreatePhpDocInfo(ClassConst $classConst): PhpDocInfo
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classConst->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($classConst);
        }

        return $phpDocInfo;
    }
}

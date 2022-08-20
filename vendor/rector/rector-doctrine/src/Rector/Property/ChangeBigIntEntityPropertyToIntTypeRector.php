<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Rector\NodeTypeResolver\ValueObject\OldToNewType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#bigint
 *
 * @see \Rector\Doctrine\Tests\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector\ChangeBigIntEntityPropertyToIntTypeRectorTest
 */
final class ChangeBigIntEntityPropertyToIntTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer
     */
    private $docBlockClassRenamer;
    public function __construct(DocBlockClassRenamer $docBlockClassRenamer)
    {
        $this->docBlockClassRenamer = $docBlockClassRenamer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change database type "bigint" for @var/type declaration to string', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeEntity
{
    /**
     * @var int|null
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SomeEntity
{
    /**
     * @var string|null
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $bigNumber;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\ORM\\Mapping\\Column');
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $typeArrayItemNode = $doctrineAnnotationTagValueNode->getValue('type');
        if (!$typeArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        if ($typeArrayItemNode->value !== 'bigint') {
            return null;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        $oldToNewTypes = [new OldToNewType(new IntegerType(), new StringType()), new OldToNewType(new FloatType(), new StringType()), new OldToNewType(new BooleanType(), new StringType())];
        $this->docBlockClassRenamer->renamePhpDocType($phpDocInfo, $oldToNewTypes);
        return $node;
    }
}

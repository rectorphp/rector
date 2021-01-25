<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://www.luzanky.cz/ for sponsoring this rule
 *
 * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#bigint
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\ChangeBigIntEntityPropertyToIntTypeRector\ChangeBigIntEntityPropertyToIntTypeRectorTest
 */
final class ChangeBigIntEntityPropertyToIntTypeRector extends AbstractRector
{
    /**
     * @var DocBlockClassRenamer
     */
    private $docBlockClassRenamer;

    public function __construct(DocBlockClassRenamer $docBlockClassRenamer)
    {
        $this->docBlockClassRenamer = $docBlockClassRenamer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change database type "bigint" for @var/type declaration to string',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $columnTagValueNode = $phpDocInfo->getByType(ColumnTagValueNode::class);
        if (! $columnTagValueNode instanceof ColumnTagValueNode) {
            return null;
        }

        if ($columnTagValueNode->getType() !== 'bigint') {
            return null;
        }

        $attributeAwareVarTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $attributeAwareVarTagValueNode instanceof VarTagValueNode) {
            return null;
        }

        $this->docBlockClassRenamer->renamePhpDocTypes(
            $phpDocInfo,
            [new IntegerType(), new FloatType(), new BooleanType()],
            new StringType(),
            $node
        );

        return $node;
    }
}

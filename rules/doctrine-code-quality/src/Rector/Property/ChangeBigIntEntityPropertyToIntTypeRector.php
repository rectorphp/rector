<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\DoctrinePropertyAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockClassRenamer;

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
     * @var DoctrinePropertyAnalyzer
     */
    private $doctrinePropertyAnalyzer;

    /**
     * @var DocBlockClassRenamer
     */
    private $docBlockClassRenamer;

    public function __construct(
        DoctrinePropertyAnalyzer $doctrinePropertyAnalyzer,
        DocBlockClassRenamer $docBlockClassRenamer
    ) {
        $this->doctrinePropertyAnalyzer = $doctrinePropertyAnalyzer;
        $this->docBlockClassRenamer = $docBlockClassRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change database type "bigint" for @var/type declaration to string', [
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
        $columnTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineColumnTagValueNode($node);
        if ($columnTagValueNode === null) {
            return null;
        }

        if ($columnTagValueNode->getType() !== 'bigint') {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $attributeAwareVarTagValueNode = $phpDocInfo->getVarTagValueNode();
        if ($attributeAwareVarTagValueNode === null) {
            return null;
        }

        $this->docBlockClassRenamer->renamePhpDocTypes(
            $phpDocInfo->getPhpDocNode(),
            [new IntegerType(), new FloatType(), new BooleanType()],
            new StringType(),
            $node
        );

        return $node;
    }
}

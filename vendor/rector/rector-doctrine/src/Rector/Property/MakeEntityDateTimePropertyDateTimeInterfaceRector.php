<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeManipulator\PropertyTypeManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see related to maker bundle https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
 *
 * @see \Rector\Doctrine\Tests\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector\MakeEntityDateTimePropertyDateTimeInterfaceRectorTest
 */
final class MakeEntityDateTimePropertyDateTimeInterfaceRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\PropertyTypeManipulator
     */
    private $propertyTypeManipulator;
    public function __construct(\Rector\Doctrine\NodeManipulator\PropertyTypeManipulator $propertyTypeManipulator)
    {
        $this->propertyTypeManipulator = $propertyTypeManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Make maker bundle generate DateTime property accept DateTimeInterface too', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTime|null
     */
    private $bornAt;

    public function setBornAt(DateTimeInterface $bornAt)
    {
        $this->bornAt = $bornAt;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface|null
     */
    private $bornAt;

    public function setBornAt(DateTimeInterface $bornAt)
    {
        $this->bornAt = $bornAt;
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
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if ($phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            $varType = $phpDocInfo->getVarType();
            if ($varType instanceof \PHPStan\Type\UnionType) {
                $varType = \PHPStan\Type\TypeCombinator::removeNull($varType);
            }
            if (!$varType->equals(new \PHPStan\Type\ObjectType('DateTime'))) {
                return null;
            }
            $this->propertyTypeManipulator->changePropertyType($node, 'DateTime', 'DateTimeInterface');
            return $node;
        }
        return null;
    }
}

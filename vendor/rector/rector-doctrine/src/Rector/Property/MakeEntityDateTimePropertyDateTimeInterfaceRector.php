<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\SetterClassMethodAnalyzer;
use Rector\Doctrine\NodeManipulator\PropertyTypeManipulator;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see related to maker bundle https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
 *
 * @see \Rector\Doctrine\Tests\Rector\Property\MakeEntityDateTimePropertyDateTimeInterfaceRector\MakeEntityDateTimePropertyDateTimeInterfaceRectorTest
 */
final class MakeEntityDateTimePropertyDateTimeInterfaceRector extends AbstractRector
{
    /**
     * @var SetterClassMethodAnalyzer
     */
    private $setterClassMethodAnalyzer;
    /**
     * @var PropertyTypeManipulator
     */
    private $propertyTypeManipulator;
    /**
     * @var PropertyTypeInferer
     */
    private $propertyTypeInferer;
    public function __construct(SetterClassMethodAnalyzer $setterClassMethodAnalyzer, PropertyTypeManipulator $propertyTypeManipulator, PropertyTypeInferer $propertyTypeInferer)
    {
        $this->setterClassMethodAnalyzer = $setterClassMethodAnalyzer;
        $this->propertyTypeManipulator = $propertyTypeManipulator;
        $this->propertyTypeInferer = $propertyTypeInferer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make maker bundle generate DateTime property accept DateTimeInterface too', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $inferredType = $this->propertyTypeInferer->inferProperty($node);
        if ($inferredType instanceof UnionType) {
            $inferredType = TypeCombinator::removeNull($inferredType);
        }
        $dateTimeObjectType = new ObjectType('DateTimeInterface');
        if (!$dateTimeObjectType->equals($inferredType)) {
            return null;
        }
        if (!$this->isObjectType($node, new ObjectType('DateTime'))) {
            return null;
        }
        $this->propertyTypeManipulator->changePropertyType($node, 'DateTime', 'DateTimeInterface');
        return $node;
    }
}

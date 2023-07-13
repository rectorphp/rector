<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\TypeAnalyzer\DoctrineCollectionTypeAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromDoctrineCollectionRector\TypedPropertyFromDoctrineCollectionRectorTest
 */
final class TypedPropertyFromDoctrineCollectionRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\DoctrineCollectionTypeAnalyzer
     */
    private $doctrineCollectionTypeAnalyzer;
    public function __construct(DoctrineCollectionTypeAnalyzer $doctrineCollectionTypeAnalyzer)
    {
        $this->doctrineCollectionTypeAnalyzer = $doctrineCollectionTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add typed property based on Doctrine collection', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;
use App\Entity\TrainingTerm;

/**
 * @ORM\Entity
 */
class DoctrineCollection
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TrainingTerm", mappedBy="training")
     * @var TrainingTerm[]|Collection
     */
    private $trainingTerms;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;
use App\Entity\TrainingTerm;

/**
 * @ORM\Entity
 */
class DoctrineCollection
{
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TrainingTerm", mappedBy="training")
     * @var TrainingTerm[]|Collection
     */
    private \Doctrine\Common\Collections\Collection $trainingTerms;
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
        if ($node->type !== null) {
            return null;
        }
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$propertyPhpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $varTagValueNode = $propertyPhpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        $varTagType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $node);
        if (!$this->doctrineCollectionTypeAnalyzer->detect($varTagType)) {
            return null;
        }
        $node->type = new FullyQualified('Doctrine\\Common\\Collections\\Collection');
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_74;
    }
}

<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Use more complete \Rector\Doctrine\CodeQuality\Rector\Class_\ExplicitRelationCollectionRector instead
 */
final class TypedPropertyFromDoctrineCollectionRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    /**
     * @var bool
     */
    private $hasWarned = \false;
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
        if ($this->hasWarned) {
            return null;
        }
        \trigger_error(\sprintf('The "%s" rule was deprecated, as its functionality caused bugs. Without knowing the full dependency tree, its risky to change. Use "%s" instead', self::class, 'https://github.com/rectorphp/swiss-knife#4-finalize-classes-without-children'));
        \sleep(3);
        $this->hasWarned = \true;
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_74;
    }
}

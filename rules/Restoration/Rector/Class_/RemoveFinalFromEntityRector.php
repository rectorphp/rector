<?php

declare (strict_types=1);
namespace Rector\Restoration\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Restoration\Rector\Class_\RemoveFinalFromEntityRector\RemoveFinalFromEntityRectorTest
 */
final class RemoveFinalFromEntityRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const ALLOWED_ANNOTATIONS = ['Doctrine\\ORM\\Mapping\\Entity', 'Doctrine\\ORM\\Mapping\\Embeddable'];
    /**
     * @var string[]
     */
    private const ALLOWED_ATTRIBUTES = ['Doctrine\\ORM\\Mapping\\Entity', 'Doctrine\\ORM\\Mapping\\Embeddable'];
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    public function __construct(VisibilityManipulator $visibilityManipulator, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove final from Doctrine entities', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (!$phpDocInfo->hasByAnnotationClasses(self::ALLOWED_ANNOTATIONS) && !$this->phpAttributeAnalyzer->hasPhpAttributes($node, self::ALLOWED_ATTRIBUTES)) {
            return null;
        }
        if (!$node->isFinal()) {
            return null;
        }
        $this->visibilityManipulator->removeFinal($node);
        return $node;
    }
}

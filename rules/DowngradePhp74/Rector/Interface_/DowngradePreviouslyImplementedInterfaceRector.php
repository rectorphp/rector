<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\Interface_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Rector\AbstractRector;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector\DowngradePreviouslyImplementedInterfaceRectorTest
 */
final class DowngradePreviouslyImplementedInterfaceRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    public function __construct(\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade previously implemented interface', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
interface ContainerExceptionInterface extends Throwable
{
}

interface ExceptionInterface extends ContainerExceptionInterface, Throwable
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
interface ContainerExceptionInterface extends Throwable
{
}

interface ExceptionInterface extends ContainerExceptionInterface
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
        return [\PhpParser\Node\Stmt\Interface_::class];
    }
    /**
     * @param Interface_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $extends = $node->extends;
        if ($extends === []) {
            return null;
        }
        if (\count($extends) === 1) {
            return null;
        }
        $collectInterfaces = [];
        $isCleaned = \false;
        foreach ($extends as $key => $extend) {
            if (!$extend instanceof \PhpParser\Node\Name\FullyQualified) {
                continue;
            }
            if (\in_array($extend->toString(), $collectInterfaces, \true)) {
                unset($extends[$key]);
                $isCleaned = \true;
                continue;
            }
            $collectInterfaces = \array_merge($collectInterfaces, $this->familyRelationsAnalyzer->getClassLikeAncestorNames($extend));
        }
        if (!$isCleaned) {
            return null;
        }
        $node->extends = $extends;
        return $node;
    }
}

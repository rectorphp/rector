<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveUselessVarTagRector\RemoveUselessVarTagRectorTest
 */
final class RemoveUselessVarTagRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VarTagRemover $varTagRemover;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(VarTagRemover $varTagRemover, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->varTagRemover = $varTagRemover;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused `@var` annotation for properties and class constants', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var string
     */
    public string $name = 'name';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public string $name = 'name';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class, ClassConst::class];
    }
    /**
     * @param Property|ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $hasChanged = $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $node);
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}

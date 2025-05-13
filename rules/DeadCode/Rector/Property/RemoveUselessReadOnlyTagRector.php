<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveUselessReadOnlyTagRector\RemoveUselessReadOnlyTagRectorTest
 */
final class RemoveUselessReadOnlyTagRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(VisibilityManipulator $visibilityManipulator, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove useless `@readonly` annotation on native readonly type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @readonly
     */
    private readonly string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private readonly string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
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
        return [Class_::class, Property::class, Param::class];
    }
    /**
     * @param Class_|Property|Param $node
     */
    public function refactor(Node $node) : ?Node
    {
        // for param, only on property promotion
        if ($node instanceof Param && !$node->isPromoted()) {
            return null;
        }
        if (!$this->visibilityManipulator->isReadonly($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $readonlyDoc = $phpDocInfo->getByName('readonly');
        if (!$readonlyDoc instanceof PhpDocTagNode) {
            return null;
        }
        if (!$readonlyDoc->value instanceof GenericTagValueNode) {
            return null;
        }
        if ($readonlyDoc->value->value !== '') {
            return null;
        }
        $phpDocInfo->removeByName('readonly');
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::READONLY_PROPERTY;
    }
}

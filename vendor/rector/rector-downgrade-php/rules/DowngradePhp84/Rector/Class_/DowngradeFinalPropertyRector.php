<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\Class_;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/property-hooks#final_properties
 *
 * @see \Rector\Tests\DowngradePhp84\Rector\Class_\DowngradeFinalPropertyRector\DowngradeFinalPropertyRectorTest
 */
final class DowngradeFinalPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @var string
     */
    private const TAGNAME = 'final';
    public function __construct(DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change final property to @final tag', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public final $foo = 'bar';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @final
     */
    public $foo = 'bar';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Trait_::class];
    }
    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$property->isFinal()) {
                continue;
            }
            $this->downgradeFinal($property);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function downgradeFinal(Property $property): void
    {
        $property->flags &= ~Modifiers::FINAL;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($phpDocInfo->hasByName(self::TAGNAME)) {
            return;
        }
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@' . self::TAGNAME, new GenericTagValueNode('')));
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
    }
}

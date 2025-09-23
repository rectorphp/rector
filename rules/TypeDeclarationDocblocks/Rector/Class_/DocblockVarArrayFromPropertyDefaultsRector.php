<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromPropertyDefaultsRector\DocblockVarArrayFromPropertyDefaultsRectorTest
 */
final class DocblockVarArrayFromPropertyDefaultsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @var array docblock to array property based on iterable default value', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private array $items = [1, 2, 3];
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private array $items = [1, 2, 3];
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$property->type instanceof Identifier) {
                continue;
            }
            if (!$this->isName($property->type, 'array')) {
                continue;
            }
            if (count($property->props) > 1) {
                continue;
            }
            $soleProperty = $property->props[0];
            if (!$soleProperty->default instanceof Array_) {
                continue;
            }
            $propertyDefaultType = $this->getType($soleProperty->default);
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            // type is already known
            if ($propertyPhpDocInfo->getVarTagValueNode() instanceof VarTagValueNode) {
                continue;
            }
            if ($this->nodeDocblockTypeDecorator->decorateGenericIterableVarType($propertyDefaultType, $propertyPhpDocInfo, $property)) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}

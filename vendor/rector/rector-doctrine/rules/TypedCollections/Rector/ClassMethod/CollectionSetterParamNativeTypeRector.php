<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\TypedCollections\DocBlockAnalyzer\CollectionTagValueNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\CollectionSetterParamNativeTypeRector\CollectionSetterParamNativeTypeRectorTest
 */
final class CollectionSetterParamNativeTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private CollectionTagValueNodeAnalyzer $collectionTagValueNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, CollectionTagValueNodeAnalyzer $collectionTagValueNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->collectionTagValueNodeAnalyzer = $collectionTagValueNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add native param type to a Collection setter', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private $items;

    /**
     * @param Collection<int, string> $items
     */
    public function setItems($items): void
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private $items;

    /**
     * @param Collection<int, string> $items
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        if ($node->isAbstract()) {
            return null;
        }
        $hasChanged = \false;
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        if ($classMethodPhpDocInfo->getParamTagValueNodes() === []) {
            return null;
        }
        foreach ($node->params as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            $paramTagValueNode = $classMethodPhpDocInfo->getParamTagValueByName($this->getName($param));
            if (!$paramTagValueNode instanceof ParamTagValueNode) {
                continue;
            }
            if (!$this->collectionTagValueNodeAnalyzer->detect($paramTagValueNode)) {
                continue;
            }
            $hasChanged = \true;
            $param->type = new FullyQualified(DoctrineClass::COLLECTION);
            if ($param->default instanceof Expr) {
                // remove default param, as no longer needed; empty collection should be passed instead
                $param->default = null;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}

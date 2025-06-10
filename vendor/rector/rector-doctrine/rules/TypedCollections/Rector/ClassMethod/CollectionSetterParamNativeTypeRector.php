<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\TypedCollections\DocBlockAnalyzer\CollectionTagValueNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
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
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private CollectionTagValueNodeAnalyzer $collectionTagValueNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, CollectionTagValueNodeAnalyzer $collectionTagValueNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
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
        $isInTests = $this->testsNodeAnalyzer->isInTestClass($node);
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
            // fix reprint position of type
            $param->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            // make nullable only 1st param, as others might require a null
            if ($param->default instanceof Expr) {
                if ($isInTests === \false) {
                    // remove default param, as no longer needed; empty collection should be passed instead
                    $param->default = null;
                } else {
                    // make type explicitly nullable
                    $collectionFullyQualified = new FullyQualified(DoctrineClass::COLLECTION);
                    $param->type = new NullableType($collectionFullyQualified);
                    $param->default = new ConstFetch(new Name('null'));
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}

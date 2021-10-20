<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassLike\RemoveAnnotationRector\RemoveAnnotationRectorTest
 */
final class RemoveAnnotationRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_REMOVE = 'annotations_to_remove';
    /**
     * @var string[]
     */
    private $annotationsToRemove = [];
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove annotation by names', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
/**
 * @method getName()
 */
final class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
}
CODE_SAMPLE
, [self::ANNOTATIONS_TO_REMOVE => ['method']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassLike::class, \PhpParser\Node\FunctionLike::class, \PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Stmt\ClassConst::class];
    }
    /**
     * @param ClassLike|FunctionLike|Property|ClassConst $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->annotationsToRemove === []) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($this->annotationsToRemove as $annotationToRemove) {
            $this->phpDocTagRemover->removeByName($phpDocInfo, $annotationToRemove);
            if (!\is_a($annotationToRemove, \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode::class, \true)) {
                continue;
            }
            $phpDocInfo->removeByType($annotationToRemove);
        }
        if ($phpDocInfo->hasChanged()) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED, \true);
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, string[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $annotationsToRemove = $configuration[self::ANNOTATIONS_TO_REMOVE] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allString($annotationsToRemove);
        $this->annotationsToRemove = $annotationsToRemove;
    }
}

<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Property;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\InlineSimplePropertyAnnotationRectorTest
 *
 * rector-src dev note:
 *
 *      Do not register to coding-style config/set/coding-style.php set
 *      as it will always conflict with ECS use of \PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer
 *      so rectify CI will always rolled back the change
 */
final class InlineSimplePropertyAnnotationRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private $annotationsToConsiderForInlining = ['@var', '@phpstan-var', '@psalm-var'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Inline simple @var annotations (or other annotations) when they are the only thing in the phpdoc', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @phpstan-var string
     */
    private const TEXT = 'text';

    /**
     * @var DateTime[]
     */
    private ?array $dateTimes;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /** @phpstan-var string */
    private const TEXT = 'text';

    /** @var DateTime[] */
    private ?array $dateTimes;
}
CODE_SAMPLE
, ['var', 'phpstan-var'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class, ClassConst::class];
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        $this->annotationsToConsiderForInlining = \array_map(static function (string $annotation) : string {
            return '@' . \ltrim($annotation, '@');
        }, $configuration);
    }
    /**
     * @param Property|ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipNode($node)) {
            return null;
        }
        $comments = $node->getAttribute(AttributeKey::COMMENTS, []);
        if ((\is_array($comments) || $comments instanceof \Countable ? \count($comments) : 0) !== 1) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (\count($phpDocInfo->getPhpDocNode()->children) > 1) {
            return null;
        }
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $tags = $phpDocNode->getTags();
        if (\count($tags) !== 1) {
            return null;
        }
        // The first value may not be at index 0
        $phpDocTagNode = \reset($tags);
        if (!\in_array($phpDocTagNode->name, $this->annotationsToConsiderForInlining, \true)) {
            return null;
        }
        if (\strpos((string) $phpDocTagNode, "\n") !== \false) {
            return null;
        }
        // Handle edge cases where stringified tag is not same as it was originally
        /** @var Doc $comment */
        $comment = $comments[0];
        if (\strpos($comment->getText(), (string) $phpDocTagNode) === \false) {
            return null;
        }
        // Creating new node is the only way to enforce the "singleLined" property AFAIK
        $newPhpDocInfo = $this->phpDocInfoFactory->createEmpty($node);
        $newPhpDocInfo->makeSingleLined();
        $newPhpDocNode = $newPhpDocInfo->getPhpDocNode();
        $newPhpDocNode->children = [$phpDocTagNode];
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassConst|\PhpParser\Node\Stmt\Property $node
     */
    private function shouldSkipNode($node) : bool
    {
        if ($node instanceof Property && \count($node->props) !== 1) {
            return \true;
        }
        return $node instanceof ClassConst && \count($node->consts) !== 1;
    }
}

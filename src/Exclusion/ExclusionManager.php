<?php

declare (strict_types=1);
namespace Rector\Core\Exclusion;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see \Rector\Core\Tests\Exclusion\ExclusionManagerTest
 */
final class ExclusionManager
{
    /**
     * @var string
     * @see https://regex101.com/r/DKW6RE/1
     */
    private const NO_RECTOR_START_REGEX = '#@noRector$#';
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @param class-string<PhpRectorInterface> $rectorClass
     */
    public function isNodeSkippedByRector(Node $node, string $rectorClass) : bool
    {
        if ($node instanceof PropertyProperty || $node instanceof Const_) {
            $node = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$node instanceof Node) {
                return \false;
            }
        }
        if ($this->hasNoRectorPhpDocTagMatch($node, $rectorClass)) {
            return \true;
        }
        if ($node instanceof Stmt) {
            return \false;
        }
        // recurse up until a Stmt node is found since it might contain a noRector
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode === null) {
            return \false;
        }
        return $this->isNodeSkippedByRector($parentNode, $rectorClass);
    }
    /**
     * @param class-string<PhpRectorInterface> $rectorClass
     */
    private function hasNoRectorPhpDocTagMatch(Node $node, string $rectorClass) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        /** @var PhpDocTagNode[] $noRectorTags */
        $noRectorTags = \array_merge($phpDocInfo->getTagsByName('noRector'), $phpDocInfo->getTagsByName('norector'));
        if ($this->matchesNoRectorTag($noRectorTags, $rectorClass)) {
            return \true;
        }
        return $this->matchesNoRectorComment($node, $rectorClass);
    }
    /**
     * @param PhpDocTagNode[] $noRectorPhpDocTagNodes
     * @param class-string<RectorInterface> $rectorClass
     */
    private function matchesNoRectorTag(array $noRectorPhpDocTagNodes, string $rectorClass) : bool
    {
        foreach ($noRectorPhpDocTagNodes as $noRectorPhpDocTagNode) {
            if (!$noRectorPhpDocTagNode->value instanceof GenericTagValueNode) {
                throw new ShouldNotHappenException();
            }
            $description = $noRectorPhpDocTagNode->value->value;
            if ($description === '') {
                return \true;
            }
            $description = \ltrim($description, '\\');
            if ($description === $rectorClass) {
                return \true;
            }
            if (!\is_a($description, RectorInterface::class, \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    private function matchesNoRectorComment(Node $node, string $rectorClass) : bool
    {
        foreach ($node->getComments() as $comment) {
            if (StringUtils::isMatch($comment->getText(), self::NO_RECTOR_START_REGEX)) {
                return \true;
            }
            $noRectorWithRule = '#@noRector \\\\?' . \preg_quote($rectorClass, '#') . '$#';
            if (StringUtils::isMatch($comment->getText(), $noRectorWithRule)) {
                return \true;
            }
        }
        return \false;
    }
}

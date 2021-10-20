<?php

declare (strict_types=1);
namespace Rector\Core\Exclusion;

use RectorPrefix20211020\Nette\Utils\Strings;
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
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function isNodeSkippedByRector(\PhpParser\Node $node, \Rector\Core\Contract\Rector\PhpRectorInterface $phpRector) : bool
    {
        if ($node instanceof \PhpParser\Node\Stmt\PropertyProperty || $node instanceof \PhpParser\Node\Const_) {
            $node = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$node instanceof \PhpParser\Node) {
                return \false;
            }
        }
        if ($this->hasNoRectorPhpDocTagMatch($node, $phpRector)) {
            return \true;
        }
        if ($node instanceof \PhpParser\Node\Stmt) {
            return \false;
        }
        // recurse up until a Stmt node is found since it might contain a noRector
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentNode === null) {
            return \false;
        }
        return $this->isNodeSkippedByRector($parentNode, $phpRector);
    }
    private function hasNoRectorPhpDocTagMatch(\PhpParser\Node $node, \Rector\Core\Contract\Rector\PhpRectorInterface $phpRector) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        /** @var PhpDocTagNode[] $noRectorTags */
        $noRectorTags = \array_merge($phpDocInfo->getTagsByName('noRector'), $phpDocInfo->getTagsByName('norector'));
        $rectorClass = \get_class($phpRector);
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
            if (!$noRectorPhpDocTagNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $description = $noRectorPhpDocTagNode->value->value;
            if ($description === '') {
                return \true;
            }
            $description = \ltrim($description, '\\');
            if ($description === $rectorClass) {
                return \true;
            }
            if (!\is_a($description, \Rector\Core\Contract\Rector\RectorInterface::class, \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    private function matchesNoRectorComment(\PhpParser\Node $node, string $rectorClass) : bool
    {
        foreach ($node->getComments() as $comment) {
            if (\RectorPrefix20211020\Nette\Utils\Strings::match($comment->getText(), self::NO_RECTOR_START_REGEX)) {
                return \true;
            }
            $noRectorWithRule = '#@noRector \\\\?' . \preg_quote($rectorClass, '#') . '$#';
            if (\RectorPrefix20211020\Nette\Utils\Strings::match($comment->getText(), $noRectorWithRule)) {
                return \true;
            }
        }
        return \false;
    }
}

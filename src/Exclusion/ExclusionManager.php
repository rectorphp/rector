<?php

declare(strict_types=1);

namespace Rector\Core\Exclusion;

use Nette\Utils\Strings;
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

    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory
    ) {
    }

    public function isNodeSkippedByRector(Node $node, PhpRectorInterface $phpRector): bool
    {
        if ($node instanceof PropertyProperty || $node instanceof Const_) {
            $node = $node->getAttribute(AttributeKey::PARENT_NODE);
        }

        if ($this->hasNoRectorPhpDocTagMatch($node, $phpRector)) {
            return true;
        }

        if ($node instanceof Stmt) {
            return false;
        }

        // recurse up until a Stmt node is found since it might contain a noRector
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode === null) {
            return false;
        }

        return $this->isNodeSkippedByRector($parentNode, $phpRector);
    }

    private function hasNoRectorPhpDocTagMatch(Node $node, PhpRectorInterface $phpRector): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        /** @var PhpDocTagNode[] $noRectorTags */
        $noRectorTags = array_merge($phpDocInfo->getTagsByName('noRector'), $phpDocInfo->getTagsByName('norector'));

        $rectorClass = $phpRector::class;

        if ($this->matchesNoRectorTag($noRectorTags, $rectorClass)) {
            return true;
        }

        return $this->matchesNoRectorComment($node, $rectorClass);
    }

    /**
     * @param PhpDocTagNode[] $noRectorPhpDocTagNodes
     * @param class-string<RectorInterface> $rectorClass
     */
    private function matchesNoRectorTag(array $noRectorPhpDocTagNodes, string $rectorClass): bool
    {
        foreach ($noRectorPhpDocTagNodes as $noRectorPhpDocTagNode) {
            if (! $noRectorPhpDocTagNode->value instanceof GenericTagValueNode) {
                throw new ShouldNotHappenException();
            }

            $description = $noRectorPhpDocTagNode->value->value;
            if ($description === '') {
                return true;
            }

            $description = ltrim($description, '\\');
            if ($description === $rectorClass) {
                return true;
            }

            if (! is_a($description, RectorInterface::class, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    private function matchesNoRectorComment(Node $node, string $rectorClass): bool
    {
        foreach ($node->getComments() as $comment) {
            if (Strings::match($comment->getText(), self::NO_RECTOR_START_REGEX)) {
                return true;
            }

            $noRectorWithRule = '#@noRector \\\\?' . preg_quote($rectorClass, '#') . '$#';
            if (Strings::match($comment->getText(), $noRectorWithRule)) {
                return true;
            }
        }

        return false;
    }
}

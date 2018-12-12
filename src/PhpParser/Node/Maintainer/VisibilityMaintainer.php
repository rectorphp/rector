<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Maintainer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\InvalidNodeTypeException;
use function Safe\sprintf;

final class VisibilityMaintainer
{
    /**
     * @var string[]
     */
    private $allowedNodeTypes = [ClassMethod::class, Property::class, ClassConst::class];

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function replaceVisibilityFlag(Node $node, string $visibility): void
    {
        if ($visibility !== 'static') {
            $this->removeOriginalVisibilityFromFlags($node);
        }

        $this->addVisibilityFlag($node, $visibility);
    }

    /**
     * This way "abstract", "static", "final" are kept
     *
     * @param ClassMethod|Property|ClassConst $node
     */
    private function removeOriginalVisibilityFromFlags(Node $node): void
    {
        $this->ensureIsClassMethodOrProperty($node, __METHOD__);

        if ($node->isPublic()) {
            $node->flags -= Class_::MODIFIER_PUBLIC;
        }

        if ($node->isProtected()) {
            $node->flags -= Class_::MODIFIER_PROTECTED;
        }

        if ($node->isPrivate()) {
            $node->flags -= Class_::MODIFIER_PRIVATE;
        }
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    private function addVisibilityFlag(Node $node, string $visibility): void
    {
        $this->ensureIsClassMethodOrProperty($node, __METHOD__);

        if ($visibility === 'public') {
            $node->flags |= Class_::MODIFIER_PUBLIC;
        }

        if ($visibility === 'protected') {
            $node->flags |= Class_::MODIFIER_PROTECTED;
        }

        if ($visibility === 'private') {
            $node->flags |= Class_::MODIFIER_PRIVATE;
        }

        if ($visibility === 'static') {
            $node->flags |= Class_::MODIFIER_STATIC;
        }

    }

    private function ensureIsClassMethodOrProperty(Node $node, string $location): void
    {
        foreach ($this->allowedNodeTypes as $allowedNodeType) {
            if (is_a($node, $allowedNodeType, true)) {
                return;
            }
        }

        throw new InvalidNodeTypeException(sprintf(
            '"%s" only accepts "%s" types. "%s" given.',
            $location,
            implode('", "', $this->allowedNodeTypes),
            get_class($node)
        ));
    }
}

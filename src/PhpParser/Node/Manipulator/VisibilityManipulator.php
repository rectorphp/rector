<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\InvalidNodeTypeException;

final class VisibilityManipulator
{
    /**
     * @var string[]
     */
    private const ALLOWED_NODE_TYPES = [ClassMethod::class, Property::class, ClassConst::class, Class_::class];

    /**
     * @var string
     */
    private const STATIC = 'static';

    /**
     * @var string
     */
    private const FINAL = 'final';

    /**
     * @var string
     */
    private const ABSTRACT = 'abstract';

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makeStatic(Node $node): void
    {
        $this->addVisibilityFlag($node, self::STATIC);
    }

    /**
     * @param ClassMethod|Class_ $node
     */
    public function makeAbstract(Node $node): void
    {
        $this->addVisibilityFlag($node, self::ABSTRACT);
    }

    /**
     * @param ClassMethod|Property $node
     */
    public function makeNonStatic(Node $node): void
    {
        if (! $node->isStatic()) {
            return;
        }

        $node->flags -= Class_::MODIFIER_STATIC;
    }

    /**
     * @param Class_|ClassMethod $node
     */
    public function makeFinal(Node $node): void
    {
        $this->addVisibilityFlag($node, self::FINAL);
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function replaceVisibilityFlag(Node $node, string $visibility): void
    {
        $visibility = strtolower($visibility);

        if ($visibility !== self::STATIC && $visibility !== self::ABSTRACT && $visibility !== self::FINAL) {
            $this->removeOriginalVisibilityFromFlags($node);
        }

        $this->addVisibilityFlag($node, $visibility);
    }

    /**
     * @param Class_|ClassMethod|Property|ClassConst $node
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

        if ($visibility === self::STATIC) {
            $node->flags |= Class_::MODIFIER_STATIC;
        }

        if ($visibility === self::ABSTRACT) {
            $node->flags |= Class_::MODIFIER_ABSTRACT;
        }

        if ($visibility === self::FINAL) {
            $node->flags |= Class_::MODIFIER_FINAL;
        }
    }

    /**
     * This way "abstract", "static", "final" are kept
     *
     * @param ClassMethod|Property|ClassConst $node
     */
    private function removeOriginalVisibilityFromFlags(Node $node): void
    {
        $this->ensureIsClassMethodOrProperty($node, __METHOD__);

        // no modifier
        if ($node->flags === 0) {
            return;
        }

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

    private function ensureIsClassMethodOrProperty(Node $node, string $location): void
    {
        foreach (self::ALLOWED_NODE_TYPES as $allowedNodeType) {
            if (is_a($node, $allowedNodeType, true)) {
                return;
            }
        }

        throw new InvalidNodeTypeException(sprintf(
            '"%s" only accepts "%s" types. "%s" given.',
            $location,
            implode('", "', self::ALLOWED_NODE_TYPES),
            get_class($node)
        ));
    }
}

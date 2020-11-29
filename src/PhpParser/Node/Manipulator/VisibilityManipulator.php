<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\InvalidNodeTypeException;
use Rector\Core\Exception\ShouldNotHappenException;

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
     * @var string[]
     */
    private const ALLOWED_VISIBILITIES = ['public', 'protected', 'private', 'static'];

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
     * This way "abstract", "static", "final" are kept
     *
     * @param ClassMethod|Property|ClassConst $node
     */
    public function removeOriginalVisibilityFromFlags(Node $node): void
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

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function changeNodeVisibility(Node $node, string $visibility): void
    {
        if ($visibility === 'public') {
            $this->makePublic($node);
        } elseif ($visibility === 'protected') {
            $this->makeProtected($node);
        } elseif ($visibility === 'private') {
            $this->makePrivate($node);
        } elseif ($visibility === 'static') {
            $this->makeStatic($node);
        } else {
            throw new ShouldNotHappenException(sprintf(
                'Visibility "%s" is not valid. Use one of: ',
                implode('", "', self::ALLOWED_VISIBILITIES)
            ));
        }
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makePublic(Node $node): void
    {
        $this->replaceVisibilityFlag($node, 'public');
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makeProtected(Node $node): void
    {
        $this->replaceVisibilityFlag($node, 'protected');
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makePrivate(Node $node): void
    {
        $this->replaceVisibilityFlag($node, 'private');
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    private function replaceVisibilityFlag(Node $node, string $visibility): void
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

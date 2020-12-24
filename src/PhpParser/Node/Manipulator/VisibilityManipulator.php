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
use Rector\Core\ValueObject\Visibility;

final class VisibilityManipulator
{
    /**
     * @var string[]
     */
    private const ALLOWED_NODE_TYPES = [ClassMethod::class, Property::class, ClassConst::class, Class_::class];

    /**
     * @var int[]
     */
    private const ALLOWED_VISIBILITIES = [
        Visibility::PUBLIC,
        Visibility::PROTECTED,
        Visibility::PRIVATE,
        Visibility::STATIC,
    ];

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makeStatic(Node $node): void
    {
        $this->addVisibilityFlag($node, Visibility::STATIC);
    }

    /**
     * @param ClassMethod|Class_ $node
     */
    public function makeAbstract(Node $node): void
    {
        $this->addVisibilityFlag($node, Visibility::ABSTRACT);
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
        $this->addVisibilityFlag($node, Visibility::FINAL);
    }

    /**
     * @param Class_|ClassMethod $node
     */
    public function makeNonFinal(Node $node): void
    {
        if (! $node->isFinal()) {
            return;
        }

        $node->flags -= Class_::MODIFIER_FINAL;
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
    public function changeNodeVisibility(Node $node, int $visibility): void
    {
        if ($visibility === Visibility::PUBLIC) {
            $this->makePublic($node);
        } elseif ($visibility === Visibility::PROTECTED) {
            $this->makeProtected($node);
        } elseif ($visibility === Visibility::PRIVATE) {
            $this->makePrivate($node);
        } elseif ($visibility === Visibility::STATIC) {
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
        $this->replaceVisibilityFlag($node, Visibility::PUBLIC);
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makeProtected(Node $node): void
    {
        $this->replaceVisibilityFlag($node, Visibility::PROTECTED);
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makePrivate(Node $node): void
    {
        $this->replaceVisibilityFlag($node, Visibility::PRIVATE);
    }

    /**
     * @param Class_|ClassMethod|Property|ClassConst $node
     */
    private function addVisibilityFlag(Node $node, int $visibility): void
    {
        $this->ensureIsClassMethodOrProperty($node, __METHOD__);
        $node->flags |= $visibility;
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

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    private function replaceVisibilityFlag(Node $node, int $visibility): void
    {
        $isStatic = $node instanceof ClassMethod && $node->isStatic();
        if ($isStatic) {
            $this->removeOriginalVisibilityFromFlags($node);
        }

        if ($visibility !== Visibility::STATIC && $visibility !== Visibility::ABSTRACT && $visibility !== Visibility::FINAL) {
            $this->removeOriginalVisibilityFromFlags($node);
        }

        $this->addVisibilityFlag($node, $visibility);

        if ($isStatic) {
            $this->makeStatic($node);
        }
    }
}

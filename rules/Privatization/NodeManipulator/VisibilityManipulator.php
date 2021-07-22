<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\InvalidNodeTypeException;
use Rector\Core\ValueObject\Visibility;
use Webmozart\Assert\Assert;

final class VisibilityManipulator
{
    /**
     * @var array<class-string<Stmt>>
     */
    private const ALLOWED_NODE_TYPES = [ClassMethod::class, Property::class, ClassConst::class, Class_::class];

    public function makeStatic(ClassMethod | Property | ClassConst $node): void
    {
        $this->addVisibilityFlag($node, Visibility::STATIC);
    }

    public function makeAbstract(ClassMethod | Class_ $node): void
    {
        $this->addVisibilityFlag($node, Visibility::ABSTRACT);
    }

    public function makeNonStatic(ClassMethod | Property $node): void
    {
        if (! $node->isStatic()) {
            return;
        }

        $node->flags -= Class_::MODIFIER_STATIC;
    }

    public function makeFinal(Class_ | ClassMethod | ClassConst $node): void
    {
        $this->addVisibilityFlag($node, Visibility::FINAL);
    }

    public function makeNonFinal(Class_ | ClassMethod $node): void
    {
        if (! $node->isFinal()) {
            return;
        }

        $node->flags -= Class_::MODIFIER_FINAL;
    }

    /**
     * This way "abstract", "static", "final" are kept
     */
    public function removeVisibility(ClassMethod | Property | ClassConst $node): void
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

    public function changeNodeVisibility(ClassMethod | Property | ClassConst $node, int $visibility): void
    {
        Assert::oneOf($visibility, [
            Visibility::PUBLIC,
            Visibility::PROTECTED,
            Visibility::PRIVATE,
            Visibility::STATIC,
            Visibility::ABSTRACT,
            Visibility::FINAL,
        ]);

        $this->replaceVisibilityFlag($node, $visibility);
    }

    public function makePublic(ClassMethod | Property | ClassConst $node): void
    {
        $this->replaceVisibilityFlag($node, Visibility::PUBLIC);
    }

    public function makeProtected(ClassMethod | Property | ClassConst $node): void
    {
        $this->replaceVisibilityFlag($node, Visibility::PROTECTED);
    }

    public function makePrivate(ClassMethod | Property | ClassConst $node): void
    {
        $this->replaceVisibilityFlag($node, Visibility::PRIVATE);
    }

    public function removeFinal(Class_ | ClassConst $node): void
    {
        $node->flags -= Class_::MODIFIER_FINAL;
    }

    private function addVisibilityFlag(Class_ | ClassMethod | Property | ClassConst $node, int $visibility): void
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
            $node::class
        ));
    }

    private function replaceVisibilityFlag(ClassMethod | Property | ClassConst $node, int $visibility): void
    {
        $isStatic = $node instanceof ClassMethod && $node->isStatic();
        if ($isStatic) {
            $this->removeVisibility($node);
        }

        if ($visibility !== Visibility::STATIC && $visibility !== Visibility::ABSTRACT && $visibility !== Visibility::FINAL) {
            $this->removeVisibility($node);
        }

        $this->addVisibilityFlag($node, $visibility);

        if ($isStatic) {
            $this->makeStatic($node);
        }
    }
}

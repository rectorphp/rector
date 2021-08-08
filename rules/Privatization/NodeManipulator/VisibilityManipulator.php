<?php

declare(strict_types=1);

namespace Rector\Privatization\NodeManipulator;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\ValueObject\Visibility;
use Webmozart\Assert\Assert;

final class VisibilityManipulator
{
    public function hasVisibility(ClassMethod | Property | ClassConst | Param $node, int $visibility): bool
    {
        return (bool) ($node->flags & $visibility);
    }

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

    public function removeAbstract(ClassMethod $node): void
    {
        $node->flags -= Class_::MODIFIER_ABSTRACT;
    }

    public function makeReadonly(Property | Param $node): void
    {
        $this->addVisibilityFlag($node, Class_::MODIFIER_READONLY);
    }

    private function addVisibilityFlag(
        Class_ | ClassMethod | Property | ClassConst | Param $node,
        int $visibility
    ): void {
        $node->flags |= $visibility;
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

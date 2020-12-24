<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\Manipulator\VisibilityManipulator;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait VisibilityTrait
{
    /**
     * @var VisibilityManipulator
     */
    private $visibilityManipulator;

    /**
     * @required
     */
    public function autowireVisibilityTrait(VisibilityManipulator $visibilityManipulator): void
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function changeNodeVisibility(Node $node, string $visibility): void
    {
        $this->visibilityManipulator->changeNodeVisibility($node, $visibility);
    }

    /**
     * @param ClassMethod|Class_ $node
     */
    public function makeFinal(Node $node): void
    {
        $this->visibilityManipulator->makeFinal($node);
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function removeVisibility(Node $node): void
    {
        $this->visibilityManipulator->removeOriginalVisibilityFromFlags($node);
    }

    /**
     * @param ClassMethod|Class_ $node
     */
    public function makeAbstract(Node $node): void
    {
        $this->visibilityManipulator->makeAbstract($node);
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makePublic(Node $node): void
    {
        $this->visibilityManipulator->makePublic($node);
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makeProtected(Node $node): void
    {
        $this->visibilityManipulator->makeProtected($node);
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makePrivate(Node $node): void
    {
        $this->visibilityManipulator->makePrivate($node);
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makeStatic(Node $node): void
    {
        $this->visibilityManipulator->makeStatic($node);
    }

    /**
     * @param ClassMethod|Property $node
     */
    public function makeNonStatic(Node $node): void
    {
        $this->visibilityManipulator->makeNonStatic($node);
    }

    /**
     * @param ClassMethod|Class_ $node
     */
    public function makeNonFinal(Node $node): void
    {
        $this->visibilityManipulator->makeNonFinal($node);
    }
}

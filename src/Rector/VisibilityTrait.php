<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Maintainer\VisibilityMaintainer;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait VisibilityTrait
{
    /**
     * @var VisibilityMaintainer
     */
    private $visibilityMaintainer;

    /**
     * @required
     */
    public function autowireVisbilityTrait(VisibilityMaintainer $visibilityMaintainer): void
    {
        $this->visibilityMaintainer = $visibilityMaintainer;
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
            $allowedVisibilities = ['public', 'protected', 'private', 'static'];

            throw new ShouldNotHappenException(sprintf(
                'Visibility "%s" is not valid. Use one of: ',
                implode('", "', $allowedVisibilities)
            ));
        }
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makePublic(Node $node): void
    {
        $this->visibilityMaintainer->replaceVisibilityFlag($node, 'public');
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makeProtected(Node $node): void
    {
        $this->visibilityMaintainer->replaceVisibilityFlag($node, 'protected');
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makePrivate(Node $node): void
    {
        $this->visibilityMaintainer->replaceVisibilityFlag($node, 'private');
    }

    /**
     * @param ClassMethod|Property|ClassConst $node
     */
    public function makeStatic(Node $node): void
    {
        $this->visibilityMaintainer->makeStatic($node);
    }
}

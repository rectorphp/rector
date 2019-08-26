<?php declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait DocBlockManipulatorTrait
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @required
     */
    public function autowireDocBlockManipulatorTrait(DocBlockManipulator $docBlockManipulator): void
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    protected function getPhpDocInfo(Node $node): ?PhpDocInfo
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        return $this->docBlockManipulator->createPhpDocInfoFromNode($node);
    }
}

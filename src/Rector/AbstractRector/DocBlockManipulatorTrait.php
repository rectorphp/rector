<?php

declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\StaticTypeMapper;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait DocBlockManipulatorTrait
{
    /**
     * @var StaticTypeMapper
     */
    protected $staticTypeMapper;

    /**
     * @var DocBlockManipulator
     */
    protected $docBlockManipulator;

    /**
     * @required
     */
    public function autowireDocBlockManipulatorTrait(
        DocBlockManipulator $docBlockManipulator,
        StaticTypeMapper $staticTypeMapper
    ): void {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    protected function getPhpDocInfo(Node $node): ?PhpDocInfo
    {
        if ($node->getAttribute(AttributeKey::PHP_DOC_INFO)) {
            return $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        }

        // @todo always use PhpDocInfo even for empty nodes, for consisteny object API; same way Nop node does
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($node);
        $node->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        return $phpDocInfo;
    }
}

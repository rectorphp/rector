<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoManipulator;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait PhpDocTrait
{
    /**
     * @var PhpDocInfoPrinter
     */
    protected $phpDocInfoPrinter;

    /**
     * @var PhpDocInfoFactory
     */
    protected $phpDocInfoFactory;

    /**
     * @var PhpDocInfoManipulator
     */
    protected $phpDocInfoManipulator;

    /**
     * @required
     */
    public function autowirePhpDocTrait(
        PhpDocInfoPrinter $phpDocInfoPrinter,
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoManipulator $phpDocInfoManipulator
    ): void {
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoManipulator = $phpDocInfoManipulator;
    }

    protected function getPhpDocTagValueNode(Node $node, string $phpDocTagNodeClass): ?PhpDocTagValueNode
    {
        return $this->phpDocInfoManipulator->getPhpDocTagValueNode($node, $phpDocTagNodeClass);
    }
}

<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class GeneratedValueTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    private $strategy;

    public function __construct(string $strategy)
    {
        $this->strategy = $strategy;
    }

    public function __toString(): string
    {
        return sprintf('(strategy="%s")', $this->strategy);
    }

    public function getShortName(): string
    {
        return '@ORM\GeneratedValue';
    }
}

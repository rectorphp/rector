<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Tree;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class TreeTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const CLASS_NAME = Tree::class;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function __toString(): string
    {
        return sprintf('(type="%s")', $this->type);
    }

    public function getType(): string
    {
        return $this->type;
    }
}

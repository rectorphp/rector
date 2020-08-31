<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class IndexTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface
{
    /**
     * @var string|null
     */
    private $tag;

    public function __construct(array $items, ?string $content = null, ?string $originalTag = null)
    {
        $this->tag = $originalTag;

        parent::__construct($items, $content);
    }

    public function getTag(): string
    {
        return $this->tag ?: $this->getShortName();
    }

    public function getShortName(): string
    {
        return '@ORM\Index';
    }
}

<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

final class IndexTagValueNode extends AbstractIndexTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\Index';

    public function getTag(): ?string
    {
        return $this->tag ?: self::SHORT_NAME;
    }
}

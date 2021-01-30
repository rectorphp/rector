<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette;

use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class NetteInjectTagValueNode extends AbstractTagValueNode implements PhpAttributableTagNodeInterface
{
    public function getShortName(): string
    {
        return 'Inject';
    }

    public function getAttributeClassName(): string
    {
        return 'Nette\DI\Attributes\Inject';
    }

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        return [];
    }
}

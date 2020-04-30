<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Loggable;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class LoggableTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function __construct(Loggable $loggable)
    {
        $this->items = get_object_vars($loggable);
    }

    public function getShortName(): string
    {
        return '@Gedmo\Loggable';
    }
}

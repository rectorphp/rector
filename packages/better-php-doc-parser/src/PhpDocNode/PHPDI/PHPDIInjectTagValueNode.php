<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\PHPDI;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class PHPDIInjectTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function getShortName(): string
    {
        return '@Inject';
    }
}

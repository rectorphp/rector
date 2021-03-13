<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

final class PHPDIInjectTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function getShortName(): string
    {
        return '@Inject';
    }
}

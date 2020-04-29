<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\PHPDI;

use DI\Annotation\Inject;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class PHPDIInjectTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function __construct(Inject $inject)
    {
        $this->items = get_object_vars($inject);
    }

    public function getShortName(): string
    {
        return '@Inject';
    }
}

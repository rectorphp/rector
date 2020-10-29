<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

final class JMSServiceValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function getShortName(): string
    {
        return '@DI\Service';
    }
}

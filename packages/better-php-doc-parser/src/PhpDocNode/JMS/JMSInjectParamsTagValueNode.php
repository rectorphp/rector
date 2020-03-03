<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\JMS;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class JMSInjectParamsTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function __construct(string $originalContent)
    {
        $this->originalContent = $originalContent;
    }

    public function __toString(): string
    {
        return $this->originalContent;
    }

    public function getShortName(): string
    {
        return '@DI\InjectParams';
    }
}

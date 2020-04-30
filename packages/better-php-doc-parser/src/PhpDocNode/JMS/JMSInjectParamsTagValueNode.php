<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\JMS;

use JMS\DiExtraBundle\Annotation\InjectParams;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class JMSInjectParamsTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function __construct(InjectParams $injectParams, string $originalContent)
    {
        $this->items = get_object_vars($injectParams);
        $this->originalContent = $originalContent;
    }

    public function getShortName(): string
    {
        return '@DI\InjectParams';
    }
}

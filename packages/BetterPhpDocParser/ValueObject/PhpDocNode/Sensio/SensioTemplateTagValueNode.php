<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Sensio;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

final class SensioTemplateTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface, SilentKeyNodeInterface
{
    public function getTemplate(): ?string
    {
        return $this->items['template'];
    }

    /**
     * @return string[]
     */
    public function getVars(): array
    {
        return $this->items['vars'] ?? [];
    }

    public function getShortName(): string
    {
        return '@Template';
    }

    public function getSilentKey(): string
    {
        return 'template';
    }
}

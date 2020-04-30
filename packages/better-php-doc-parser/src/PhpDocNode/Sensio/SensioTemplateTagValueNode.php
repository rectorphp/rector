<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Sensio;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SensioTemplateTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    public function __construct(Template $template, string $content)
    {
        $this->items = get_object_vars($template);
        $this->resolveOriginalContentSpacingAndOrder($content, 'template');
    }

    public function getTemplate(): ?string
    {
        return $this->items['template'];
    }

    public function getShortName(): string
    {
        return '@Template';
    }
}

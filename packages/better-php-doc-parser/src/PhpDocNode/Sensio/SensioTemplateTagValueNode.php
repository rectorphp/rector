<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Sensio;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SensioTemplateTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string|null
     */
    private $template;

    /**
     * @var mixed[]|null
     */
    private $owner;

    /**
     * @var mixed[]|null
     */
    private $vars;

    /**
     * @param mixed[]|null $owner
     * @param mixed[]|null $vars
     */
    public function __construct(?string $template, ?array $owner = null, ?array $vars = null)
    {
        $this->template = $template;
        $this->owner = $owner;
        $this->vars = $vars;
    }

    public function __toString(): string
    {
        $items = $this->crateItems();

        return $this->printContentItems($items);
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getShortName(): string
    {
        return '@Template';
    }

    private function crateItems(): array
    {
        $items = [];

        if ($this->template) {
            $items['template'] = sprintf('"%s"', $this->template);
        }

        if ($this->owner) {
            $items['owner'] = $this->owner;
        }

        if ($this->vars) {
            $items['vars'] = $this->vars;
        }

        return $items;
    }
}

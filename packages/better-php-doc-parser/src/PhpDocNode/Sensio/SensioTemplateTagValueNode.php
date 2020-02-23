<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Sensio;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SensioTemplateTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string
     */
    public const CLASS_NAME = Template::class;

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
        $contentItems = [];

        if ($this->template) {
            $contentItems[] = $this->template;
        }

        if ($this->owner) {
            $contentItems[] = $this->printArrayItem($this->owner, 'owner');
        }

        if ($this->vars) {
            $contentItems[] = $this->printArrayItem($this->vars, 'vars');
        }

        if ($contentItems === []) {
            return '';
        }

        return implode(', ', $contentItems);
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getShortName(): string
    {
        return '@Template';
    }
}

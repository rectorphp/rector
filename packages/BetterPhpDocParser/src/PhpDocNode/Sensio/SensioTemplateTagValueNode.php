<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Sensio;

use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SensioTemplateTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Template';

    /**
     * @var string
     */
    public const CLASS_NAME = Template::class;

    /**
     * @var string|null
     */
    private $template;

    /**
     * @var mixed[]
     */
    private $owner = [];

    /**
     * @var mixed[]
     */
    private $vars = [];

    /**
     * @param mixed[] $owner
     * @param mixed[] $vars
     */
    public function __construct(?string $template, array $owner, array $vars)
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
}

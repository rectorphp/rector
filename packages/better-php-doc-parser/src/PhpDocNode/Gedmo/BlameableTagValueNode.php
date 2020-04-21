<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class BlameableTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string|null
     */
    private $on;

    /**
     * @var mixed|null
     */
    private $value;

    /**
     * @var string|string[]
     */
    private $field;

    /**
     * @param string|string[] $field
     * @param mixed|null $value
     */
    public function __construct(?string $on, $field, $value)
    {
        $this->on = $on;
        $this->field = $field;
        $this->value = $value;
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->on !== null) {
            $contentItems['on'] = sprintf('on="%s"', $this->on);
        }

        if ($this->field) {
            $contentItems['field'] = is_array($this->field) ?
                $this->printArrayItem($this->field, 'field') : $this->field;
        }

        if ($this->value !== null) {
            $contentItems['value'] = $this->value;
        }

        return $this->printContentItems($contentItems);
    }

    public function getShortName(): string
    {
        return '@Gedmo\Blameable';
    }
}

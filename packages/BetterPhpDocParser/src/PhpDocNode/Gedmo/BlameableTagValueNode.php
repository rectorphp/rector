<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Blameable;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class BlameableTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Gedmo\Blameable';

    /**
     * @var string
     */
    public const CLASS_NAME = Blameable::class;

    /**
     * @var string|null
     */
    private $on;

    /**
     * @var string|string[]
     */
    private $field;

    /**
     * @var mixed|null
     */
    private $value;

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
            $contentItems['on'] = $this->on;
        }

        if ($this->field) {
            if (is_array($this->field)) {
                $contentItems['field'] = $this->printArrayItem($this->field, 'field');
            } else {
                $contentItems['field'] = $this->field;
            }
        }

        if ($this->value !== null) {
            $contentItems['value'] = $this->value;
        }

        return $this->printContentItems($contentItems);
    }
}

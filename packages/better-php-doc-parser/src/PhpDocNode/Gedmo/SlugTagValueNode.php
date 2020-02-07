<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\Slug;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SlugTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Gedmo\Slug';

    /**
     * @var string
     */
    public const CLASS_NAME = Slug::class;

    /**
     * @var mixed[]
     */
    private $fields = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function __toString(): string
    {
        return '(fields=' . $this->printArrayItem($this->fields) . ')';
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}

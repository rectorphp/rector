<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SlugTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
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

    public function getShortName(): string
    {
        return '@Gedmo\Slug';
    }
}

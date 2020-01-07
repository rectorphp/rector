<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Gedmo\Mapping\Annotation\SoftDeleteable;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SoftDeleteableTagValueNode extends AbstractTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Gedmo\SoftDeleteable';

    /**
     * @var string
     */
    public const CLASS_NAME = SoftDeleteable::class;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var bool
     */
    private $timeAware = false;

    /**
     * @var bool
     */
    private $hardDelete = false;

    public function __construct(string $fieldName, bool $timeAware, bool $hardDelete)
    {
        $this->fieldName = $fieldName;
        $this->timeAware = $timeAware;
        $this->hardDelete = $hardDelete;
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->fieldName !== 'deletedAt') {
            $contentItems['fieldName'] = $this->fieldName;
        }

        if ($this->timeAware !== false) {
            $contentItems['timeAware'] = sprintf('strict=%s', $this->timeAware ? 'true' : 'false');
        }

        if ($this->hardDelete !== false) {
            $contentItems['hardDelete'] = sprintf('strict=%s', $this->hardDelete ? 'true' : 'false');
        }

        return $this->printContentItems($contentItems);
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}

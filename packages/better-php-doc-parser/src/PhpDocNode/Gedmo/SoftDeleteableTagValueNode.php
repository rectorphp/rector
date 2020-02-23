<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SoftDeleteableTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
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

        if ($this->timeAware) {
            $contentItems['timeAware'] = sprintf('strict=%s', $this->timeAware ? 'true' : 'false');
        }

        if ($this->hardDelete) {
            $contentItems['hardDelete'] = sprintf('strict=%s', $this->hardDelete ? 'true' : 'false');
        }

        return $this->printContentItems($contentItems);
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getShortName(): string
    {
        return '@Gedmo\SoftDeleteable';
    }
}

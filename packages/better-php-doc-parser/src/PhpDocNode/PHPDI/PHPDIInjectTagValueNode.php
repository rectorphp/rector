<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\PHPDI;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class PHPDIInjectTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string|null
     */
    private $value;

    public function __construct(?string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        if ($this->value === null) {
            return '';
        }

        return '(' . $this->value . ')';
    }

    public function getShortName(): string
    {
        return '@Inject';
    }
}

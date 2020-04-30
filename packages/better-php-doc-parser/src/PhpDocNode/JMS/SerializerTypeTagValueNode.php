<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\JMS;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SerializerTypeTagValueNode extends AbstractTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface, SilentKeyNodeInterface
{
    public function getShortName(): string
    {
        return '@Serializer\Type';
    }

    public function changeName(string $newName): void
    {
        $this->items['name'] = $newName;
    }

    public function getName(): string
    {
        return $this->items['name'];
    }

    public function replaceName(string $oldName, string $newName): bool
    {
        $oldNamePattern = '#\b' . preg_quote($oldName, '#') . '\b#';

        $newNameValue = Strings::replace($this->items['name'], $oldNamePattern, $newName);
        if ($newNameValue !== $this->items['name']) {
            $this->changeName($newNameValue);
            return true;
        }

        return false;
    }

    public function getSilentKey(): string
    {
        return 'name';
    }
}

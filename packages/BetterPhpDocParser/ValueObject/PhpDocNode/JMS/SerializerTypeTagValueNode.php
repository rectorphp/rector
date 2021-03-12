<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

final class SerializerTypeTagValueNode extends AbstractTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface, SilentKeyNodeInterface
{
    /**
     * @var string
     */
    private const NAME = 'name';

    public function getShortName(): string
    {
        return '@Serializer\Type';
    }

    public function changeName(string $newName): void
    {
        $this->items[self::NAME] = $newName;
    }

    public function getName(): string
    {
        return $this->items[self::NAME];
    }

    public function replaceName(string $oldName, string $newName): bool
    {
        $oldNamePattern = '#\b' . preg_quote($oldName, '#') . '\b#';

        $newNameValue = Strings::replace($this->items[self::NAME], $oldNamePattern, $newName);
        if ($newNameValue !== $this->items[self::NAME]) {
            $this->changeName($newNameValue);
            return true;
        }

        return false;
    }

    public function getSilentKey(): string
    {
        return self::NAME;
    }
}

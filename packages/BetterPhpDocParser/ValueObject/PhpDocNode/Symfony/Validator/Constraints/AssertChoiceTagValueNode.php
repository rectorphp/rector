<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class AssertChoiceTagValueNode extends AbstractTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface, SilentKeyNodeInterface
{
    public function isCallbackClass(string $class): bool
    {
        return $class === ($this->items['callback'][0] ?? null);
    }

    public function changeCallbackClass(string $newClass): void
    {
        $this->items['callback'][0] = $newClass;
    }

    public function getShortName(): string
    {
        return '@Assert\Choice';
    }

    public function getSilentKey(): string
    {
        return 'choices';
    }
}

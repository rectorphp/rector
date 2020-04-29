<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Symfony\Component\Validator\Constraints\Choice;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class AssertChoiceTagValueNode extends AbstractTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface
{
    public function __construct(Choice $choice, ?string $originalContent)
    {
        $this->items = get_object_vars($choice);

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'choices');
    }

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
}

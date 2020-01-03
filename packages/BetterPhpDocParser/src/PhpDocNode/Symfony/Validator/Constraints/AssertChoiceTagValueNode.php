<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;
use Symfony\Component\Validator\Constraints\Choice;

final class AssertChoiceTagValueNode extends AbstractConstraintTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@Assert\Choice';

    /**
     * @var string
     */
    public const CLASS_NAME = Choice::class;

    /**
     * @var mixed[]|string|null
     */
    private $callback;

    /**
     * @var bool|null
     */
    private $strict;

    /**
     * @param mixed[]|string|null $callback
     */
    public function __construct($callback, ?bool $strict, string $annotationContent)
    {
        $this->callback = $callback;
        $this->strict = $strict;
        $this->resolveOriginalContentSpacingAndOrder($annotationContent);
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->callback) {
            if (is_array($this->callback)) {
                $contentItems['callback'] = $this->printArrayItem($this->callback, 'callback');
            } else {
                $contentItems['callback'] = sprintf('callback="%s"', $this->callback);
            }
        }

        if ($this->strict !== null) {
            $contentItems['strict'] = sprintf('strict=%s', $this->strict ? 'true' : 'false');
        }

        return $this->printContentItems($contentItems);
    }

    public function isCallbackClass(string $class): bool
    {
        return $class === ($this->callback[0] ?? null);
    }

    public function changeCallbackClass(string $newClass): void
    {
        $this->callback[0] = $newClass;
    }
}

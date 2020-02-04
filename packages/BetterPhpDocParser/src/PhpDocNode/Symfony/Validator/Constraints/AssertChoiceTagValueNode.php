<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;
use Symfony\Component\Validator\Constraints\Choice;

final class AssertChoiceTagValueNode extends AbstractConstraintTagValueNode implements TypeAwareTagValueNodeInterface
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
     * @var array|null
     */
    private $choices;

    /**
     * @param mixed[]|string|null $callback
     */
    public function __construct($callback, ?bool $strict, string $annotationContent, ?array $choices)
    {
        $this->callback = $callback;
        $this->strict = $strict;
        $this->resolveOriginalContentSpacingAndOrder($annotationContent);
        $this->choices = $choices;
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
        } elseif ($this->choices) {
            $contentItems[] = $this->printArrayItem($this->choices);
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

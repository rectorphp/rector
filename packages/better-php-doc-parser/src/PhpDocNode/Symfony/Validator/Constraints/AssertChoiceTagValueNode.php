<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class AssertChoiceTagValueNode extends AbstractConstraintTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface
{
    /**
     * @var mixed[]|string|null
     */
    private $callback;

    /**
     * @var mixed[]|string|null
     */
    private $choices;

    /**
     * @var bool|null
     */
    private $strict;

    /**
     * @param mixed[]|string|null $callback
     * @param mixed[]|string|null $choices
     */
    public function __construct($groups, $callback, ?bool $strict, ?string $originalContent, $choices)
    {
        $this->callback = $callback;
        $this->strict = $strict;
        $this->choices = $choices;

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'choices');

        parent::__construct($groups);
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->callback) {
            $contentItems['callback'] = $this->createCallback();
        } elseif ($this->choices) {
            $contentItems['choices'] = $this->printValueWithOptionalQuotes('choices', $this->choices);
        }

        if ($this->strict !== null) {
            $contentItems['strict'] = sprintf('strict=%s', $this->strict ? 'true' : 'false');
        }

        $contentItems = $this->appendGroups($contentItems);

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

    public function getShortName(): string
    {
        return '@Assert\Choice';
    }

    private function createCallback(): string
    {
        if (is_array($this->callback)) {
            return $this->printArrayItem($this->callback, 'callback');
        }

        return sprintf('callback="%s"', $this->callback);
    }
}

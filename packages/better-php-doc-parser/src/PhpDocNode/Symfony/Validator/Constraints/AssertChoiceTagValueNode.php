<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyValidation\AssertChoiceTagValueNodeTest
 */
final class AssertChoiceTagValueNode extends AbstractConstraintTagValueNode implements TypeAwareTagValueNodeInterface, ShortNameAwareTagInterface
{
    /**
     * @var mixed[]|string|null
     */
    private $callback;

    /**
     * @var bool|null
     */
    private $strict;

    /**
     * @var mixed[]|string|null
     */
    private $choices;

    /**
     * @var bool
     */
    private $isChoicesExplicit = true;

    /**
     * @param mixed[]|string|null $callback
     * @param mixed[]|string|null $choices
     */
    public function __construct($groups, $callback, ?bool $strict, ?string $originalContent, $choices)
    {
        $this->callback = $callback;
        $this->strict = $strict;
        $this->choices = $choices;

        if ($originalContent !== null) {
            $this->isChoicesExplicit = (bool) Strings::contains($originalContent, 'choices=');
        }

        $this->resolveOriginalContentSpacingAndOrder($originalContent);

        parent::__construct($groups);
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
            $contentItems[] = $this->createChoices();
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

    private function createChoices(): string
    {
        $content = '';
        if ($this->isChoicesExplicit) {
            $content .= 'choices=';
        }

        if (is_string($this->choices)) {
            return $content . $this->choices;
        }

        assert(is_array($this->choices));

        return $content . $this->printArrayItem($this->choices);
    }
}

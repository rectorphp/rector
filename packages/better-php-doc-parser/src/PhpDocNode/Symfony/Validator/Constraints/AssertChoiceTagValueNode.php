<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;

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
     * @var array|null
     */
    private $choices;

    /**
     * @var string|null
     */
    private $originalChoiceContent;

    /**
     * @var bool
     */
    private $hasChoiceKeywordExplicit = false;

    /**
     * @param mixed[]|string|null $callback
     */
    public function __construct($callback, ?bool $strict, string $annotationContent, ?array $choices)
    {
        $this->callback = $callback;
        $this->strict = $strict;
        $this->resolveOriginalContentSpacingAndOrder($annotationContent);
        $this->choices = $choices;

        // is default key
        $this->hasChoiceKeywordExplicit = (bool) Strings::match($annotationContent, '#choices=#');

        // hotfix for https://github.com/rectorphp/rector/issues/3044, the constant reference is actually parsed to values
        $this->originalChoiceContent = $this->resolveOriginalChoiceContent($annotationContent);
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

    private function isClassConstantReference(?string $originalChoiceContent): bool
    {
        if ($originalChoiceContent === null) {
            return false;
        }

        return Strings::contains($originalChoiceContent, '::');
    }

    private function createOriginalChoiceContent()
    {
        $content = '';
        if ($this->hasChoiceKeywordExplicit) {
            $content = 'choices=';
        }

        return $content . $this->originalChoiceContent;
    }

    private function resolveOriginalChoiceContent(string $annotationContent): ?string
    {
        $matches = Strings::match($annotationContent, '#(\(choices=|\()(?<choice_content>.*?(})?)(\)|})#ms');

        return $matches['choice_content'] ?? null;
    }

    private function createChoices(): string
    {
        if ($this->isClassConstantReference($this->originalChoiceContent)) {
            return $this->createOriginalChoiceContent();
        }

        assert(is_array($this->choices));

        return $this->printArrayItem($this->choices);
    }
}

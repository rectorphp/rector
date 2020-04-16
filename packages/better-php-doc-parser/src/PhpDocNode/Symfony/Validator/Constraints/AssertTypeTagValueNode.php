<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\Symfony\Validator\Constraints\AssertTypePhpDocNodeFactory
 */
final class AssertTypeTagValueNode extends AbstractConstraintTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string|mixed[]|null
     */
    private $type;

    /**
     * @var bool
     */
    private $isTypeQuoted = false;

    /**
     * @var bool
     */
    private $isTypeExplicit = true;

    /**
     * @param string|mixed[]|null $type
     */
    public function __construct(array $groups, ?string $message = null, $type = null, ?string $originalContent = null)
    {
        $this->type = $type;

        if ($originalContent !== null) {
            $this->isTypeExplicit = (bool) Strings::contains($originalContent, 'type=');
            $this->resolveIsQuotedType($originalContent, $type);
        }

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'type');

        parent::__construct($groups, $message);
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->type !== null) {
            $contentItems['type'] = $this->createType();
        }

        $contentItems = $this->appendGroups($contentItems);
        $contentItems = $this->appendMessage($contentItems);

        return $this->printContentItems($contentItems);
    }

    public function getShortName(): string
    {
        return '@Assert\Type';
    }

    /**
     * @param string|mixed[]|null $type
     */
    private function resolveIsQuotedType(string $originalContent, $type): void
    {
        if ($type === null) {
            return;
        }

        if (! is_string($type)) {
            return;
        }

        // @see https://regex101.com/r/VgvK8C/3/
        $quotedTypePattern = sprintf('#"%s"#', preg_quote($type, '#'));

        $this->isTypeQuoted = (bool) Strings::match($originalContent, $quotedTypePattern);
    }

    private function createType(): string
    {
        $content = '';
        if ($this->isTypeExplicit) {
            $content = 'type=';
        }

        if ($this->isTypeQuoted && is_string($this->type)) {
            return $content . '"' . $this->type . '"';
        }

        if (is_array($this->type)) {
            return $content . $this->printArrayItem($this->type);
        }

        return $content . $this->type;
    }
}

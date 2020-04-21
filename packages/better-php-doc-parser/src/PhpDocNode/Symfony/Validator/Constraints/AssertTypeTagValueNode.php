<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\Symfony\Validator\Constraints\AssertTypePhpDocNodeFactory
 *
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\SymfonyValidation\AssertTypeTagValueNodeTest
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
            $this->isTypeQuoted = $this->resolveIsValueQuoted($originalContent, $type);
        }

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'type');

        parent::__construct($groups, $message);
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->type !== null) {
            $contentItems['type'] = $this->printWithOptionalQuotes(
                'type',
                $this->type,
                $this->isTypeQuoted,
                $this->isTypeExplicit
            );
        }

        $contentItems = $this->appendGroups($contentItems);
        $contentItems = $this->appendMessage($contentItems);

        return $this->printContentItems($contentItems);
    }

    public function getShortName(): string
    {
        return '@Assert\Type';
    }
}

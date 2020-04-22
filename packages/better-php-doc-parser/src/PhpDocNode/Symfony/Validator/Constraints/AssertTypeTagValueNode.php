<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\Symfony\Validator\Constraints\AssertTypePhpDocNodeFactory
 *
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\AssertTypeTagValueNodeTest
 */
final class AssertTypeTagValueNode extends AbstractConstraintTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string|mixed[]|null
     */
    private $type;

    /**
     * @param string|mixed[]|null $type
     */
    public function __construct(array $groups, ?string $message = null, $type = null, ?string $originalContent = null)
    {
        $this->type = $type;

        $this->resolveOriginalContentSpacingAndOrder($originalContent, 'type');

        parent::__construct($groups, $message);
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->type !== null) {
            $contentItems['type'] = $this->printValueWithOptionalQuotes('type', $this->type);
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

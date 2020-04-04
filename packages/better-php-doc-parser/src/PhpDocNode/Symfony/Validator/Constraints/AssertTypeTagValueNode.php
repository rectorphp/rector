<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Symfony\Validator\Constraints;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\Symfony\PhpDocParser\Ast\PhpDoc\AbstractConstraintTagValueNode;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\Symfony\Validator\Constraints\AssertTypePhpDocNodeFactory
 */
final class AssertTypeTagValueNode extends AbstractConstraintTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var string|null
     */
    private $type;

    public function __construct(array $groups, ?string $type = null)
    {
        $this->type = $type;

        parent::__construct($groups);
    }

    public function __toString(): string
    {
        $contentItems = [];
        $contentItems['type'] = $this->type;

        $contentItems = $this->appendGroups($contentItems);

        return $this->printContentItems($contentItems);
    }

    public function getShortName(): string
    {
        return '@Assert\Type';
    }
}

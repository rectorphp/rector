<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPUnit;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory\PHPUnitDataDoesNotPerformAssertionDocNodeFactory
 */
final class PHPUnitDoesNotPerformAssertionTagNode extends PhpDocTagNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * @var string
     */
    public const NAME = '@doesNotPerformAssertions';

    public function __construct()
    {
        parent::__construct(self::NAME, new AttributeAwareGenericTagValueNode(''));
    }
}

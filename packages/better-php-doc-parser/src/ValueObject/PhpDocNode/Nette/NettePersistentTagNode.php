<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareGenericTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory\NettePersistentPhpDocNodeFactory
 */
final class NettePersistentTagNode extends PhpDocTagNode implements PhpAttributableTagNodeInterface, AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * @var string
     */
    public const NAME = '@persistent';

    public function __construct()
    {
        parent::__construct(self::NAME, new AttributeAwareGenericTagValueNode(''));
    }

    public function getShortName(): string
    {
        return self::NAME;
    }

    public function getAttributeClassName(): string
    {
        return 'Nette\Application\Attributes\Persistent';
    }

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        return [];
    }
}

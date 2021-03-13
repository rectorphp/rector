<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\GenericTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory\NettePersistentPhpDocNodeFactory
 */
final class NettePersistentTagNode extends PhpDocTagNode implements PhpAttributableTagNodeInterface
{
    /**
     * @var string
     */
    public const NAME = '@persistent';

    public function __construct()
    {
        parent::__construct(self::NAME, new GenericTagValueNode(''));
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

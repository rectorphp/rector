<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette;

use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class NetteInjectTagNode extends PhpDocTagNode implements PhpAttributableTagNodeInterface
{
    /**
     * @var string
     */
    public const NAME = '@inject';

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
        return 'Nette\DI\Attributes\Inject';
    }

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

/**
 * Use by Symfony to autowire dependencies outside constructor,
 * @see https://symfony.com/doc/current/service_container/autowiring.html#autowiring-other-methods-e-g-setters-and-public-typed-properties
 */
final class SymfonyRequiredTagNode extends PhpDocTagNode implements PhpAttributableTagNodeInterface
{
    /**
     * @var string
     */
    public const NAME = '@required';

    public function __construct()
    {
        parent::__construct(self::NAME, new GenericTagValueNode(''));
    }

    public function __toString(): string
    {
        return self::NAME;
    }

    public function getShortName(): string
    {
        return self::NAME;
    }

    public function getAttributeClassName(): string
    {
        return 'Symfony\Contracts\Service\Attribute\Required';
    }

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        return [];
    }
}

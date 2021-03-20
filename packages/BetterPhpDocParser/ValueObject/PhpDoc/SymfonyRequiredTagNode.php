<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;

/**
 * Use by Symfony to autowire dependencies outside constructor,
 * @see https://symfony.com/doc/current/service_container/autowiring.html#autowiring-other-methods-e-g-setters-and-public-typed-properties
 */
final class SymfonyRequiredTagNode extends PhpDocTagNode
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
}

<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPUnit;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;

final class PHPUnitDataProviderTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;

    /**
     * @var string
     */
    public const NAME = '@dataprovider';

    /**
     * @var string
     */
    private $method;

    public function __construct(string $method)
    {
        $this->method = $method;
    }

    public function __toString(): string
    {
        return $this->method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getMethodName(): string
    {
        return trim($this->method, '()');
    }

    public function changeMethodName(string $method): void
    {
        $this->method = $method . '()';
    }
}

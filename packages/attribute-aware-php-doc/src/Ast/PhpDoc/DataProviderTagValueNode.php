<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\PhpdocParserPrinter\Attributes\AttributesTrait;
use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;

final class DataProviderTagValueNode implements PhpDocTagValueNode, AttributeAwareInterface
{
    use AttributesTrait;

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

    public function changeMethod(string $method): void
    {
        $this->method = $method;
    }
}

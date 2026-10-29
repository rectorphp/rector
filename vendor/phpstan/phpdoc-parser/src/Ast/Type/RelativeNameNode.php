<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use InvalidArgumentException;
use function sprintf;
use function strpos;
/**
 * A class-like name like Bar or Foo\Bar that resolves against the namespace
 * and uses of the place it is written in.
 */
class RelativeNameNode implements \PHPStan\PhpDocParser\Ast\Type\NameNode
{
    use \PHPStan\PhpDocParser\Ast\Type\NameNodeTrait;
    public function __construct(string $name)
    {
        if (strpos($name, '\\') === 0) {
            throw new InvalidArgumentException(sprintf('Name %s is fully qualified, use FullyQualifiedNameNode.', $name));
        }
        $this->name = $name;
    }
    public function toCodeString(): string
    {
        return $this->name;
    }
    public function isFullyQualified(): bool
    {
        return \false;
    }
}

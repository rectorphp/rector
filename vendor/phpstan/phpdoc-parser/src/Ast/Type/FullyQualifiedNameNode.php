<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use function ltrim;
/**
 * A fully qualified class-like name like \Foo\Bar.
 * It means the same class in every namespace.
 */
class FullyQualifiedNameNode implements \PHPStan\PhpDocParser\Ast\Type\NameNode
{
    use \PHPStan\PhpDocParser\Ast\Type\NameNodeTrait;
    /**
     * @param string $name with or without a leading backslash
     */
    public function __construct(string $name)
    {
        $this->name = ltrim($name, '\\');
    }
    public function toCodeString(): string
    {
        return '\\' . $this->name;
    }
    public function isFullyQualified(): bool
    {
        return \true;
    }
}

<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

/**
 * A class-like name, as opposed to a keyword like `int`, `mixed` or
 * `non-falsy-string`, which is an IdentifierTypeNode.
 *
 * The parser never produces name nodes, because it does not know which
 * identifiers are keywords. They are created by tools that do, for example
 * when turning a resolved type back into a PHPDoc node.
 */
interface NameNode extends \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    /**
     * The name without a leading backslash, e.g. Foo\Bar.
     */
    public function toString(): string;
    /**
     * The name as written in PHPDoc, e.g. \Foo\Bar or Bar.
     */
    public function toCodeString(): string;
    /**
     * @return non-empty-list<string>
     */
    public function getParts(): array;
    public function getFirst(): string;
    public function getLast(): string;
    public function isFullyQualified(): bool;
}

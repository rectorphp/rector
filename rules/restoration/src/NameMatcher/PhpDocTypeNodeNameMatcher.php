<?php

declare(strict_types=1);

namespace Rector\Restoration\NameMatcher;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\PhpdocParserPrinter\ValueObject\TypeNode\FullyQualifiedIdentifierTypeNode;

final class PhpDocTypeNodeNameMatcher
{
    /**
     * @var NameMatcher
     */
    private $nameMatcher;

    public function __construct(NameMatcher $nameMatcher)
    {
        $this->nameMatcher = $nameMatcher;
    }

    public function matchIdentifier(string $name): ?TypeNode
    {
        $name = ltrim($name, '\\');

        $fullyQualified = $this->nameMatcher->makeNameFullyQualified($name);
        if ($fullyQualified === null) {
            return null;
        }

        return new FullyQualifiedIdentifierTypeNode($fullyQualified);
    }
}

<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Type;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;

final class FullyQualifiedNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType(): string
    {
        return FullyQualified::class;
    }

    /**
     * @param FullyQualified $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        $originalName = (string) $node->getAttribute('originalName');
        $fullyQualifiedName = $node->toString();

        // is aliased?
        if ($originalName !== $fullyQualifiedName && ! Strings::endsWith($fullyQualifiedName, '\\' . $originalName)) {
            return new AliasedObjectType($originalName, $fullyQualifiedName);
        }

        return new FullyQualifiedObjectType($node->toString());
    }
}

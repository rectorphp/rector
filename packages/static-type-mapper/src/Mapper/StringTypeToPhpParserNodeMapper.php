<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\Mapper;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use Rector\Core\Exception\NotImplementedException;

final class StringTypeToPhpParserNodeMapper
{
    /**
     * @var string[]
     */
    private const SAME_NAMED_IDENTIFIERS = ['string', 'int', 'float', 'array', 'void'];

    /**
     * @return Identifier|Name|NullableType
     */
    public function map(string $type): Node
    {
        foreach (self::SAME_NAMED_IDENTIFIERS as $sameNamedIdentifier) {
            if ($type !== $sameNamedIdentifier) {
                continue;
            }

            return new Identifier($sameNamedIdentifier);
        }

        if (Strings::contains($type, '\\') || ctype_upper($type[0])) {
            return new FullyQualified($type);
        }

        if (Strings::startsWith($type, '?')) {
            $nullableType = ltrim($type, '?');

            /** @var Identifier|Name $nameNode */
            $nameNode = $this->map($nullableType);

            return new NullableType($nameNode);
        }

        throw new NotImplementedException(sprintf('%s for "%s"', __METHOD__, $type));
    }
}

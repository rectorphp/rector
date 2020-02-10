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
     * @return Identifier|Name|NullableType
     */
    public function map(string $type): Node
    {
        if ($type === 'string') {
            return new Identifier('string');
        }

        if ($type === 'int') {
            return new Identifier('int');
        }

        if ($type === 'array') {
            return new Identifier('array');
        }

        if ($type === 'float') {
            return new Identifier('float');
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

        if ($type === 'void') {
            return new Identifier('void');
        }

        throw new NotImplementedException(sprintf('%s for "%s"', __METHOD__, $type));
    }
}

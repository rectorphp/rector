<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Utils;

use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * @api to be used outside
 */
final class Json
{
    /**
     * @param array<string, mixed> $data
     */
    public static function encode(array $data): string
    {
        $encoded = json_encode($data, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
        Assert::string($encoded);
        return $encoded . \PHP_EOL;
    }
    /**
     * @return array<string, mixed>
     */
    public static function decode(string $json): array
    {
        $decoded = json_decode($json, \true, 512, \JSON_THROW_ON_ERROR);
        Assert::isArray($decoded);
        return $decoded;
    }
}

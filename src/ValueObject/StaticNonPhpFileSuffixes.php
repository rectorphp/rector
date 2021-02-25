<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

final class StaticNonPhpFileSuffixes
{
    /**
     * @var string[]
     */
    public const SUFFIXES = ['neon', 'yaml', 'xml', 'yml', 'twig', 'latte', 'blade.php'];

    public static function getSuffixRegexPattern(): string
    {
        $quotedSuffixes = [];
        foreach (self::SUFFIXES as $self::SUFFIX) {
            $quotedSuffixes[] = preg_quote($self::SUFFIX, '#');
        }

        return '#\.(' . implode('|', $quotedSuffixes) . ')$#i';
    }
}

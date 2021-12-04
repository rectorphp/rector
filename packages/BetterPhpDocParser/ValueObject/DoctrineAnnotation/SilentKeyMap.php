<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\DoctrineAnnotation;

final class SilentKeyMap
{
    /**
     * @var array<string, string>
     */
    final public const CLASS_NAMES_TO_SILENT_KEYS = [
        'Symfony\Component\Routing\Annotation\Route' => 'path',
    ];
}

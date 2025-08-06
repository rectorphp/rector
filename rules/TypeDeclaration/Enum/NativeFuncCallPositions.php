<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Enum;

final class NativeFuncCallPositions
{
    /**
     * @var array<string, array<string, int>>
     */
    public const ARRAY_AND_CALLBACK_POSITIONS = ['array_walk' => ['array' => 0, 'callback' => 1], 'array_map' => ['array' => 1, 'callback' => 0], 'usort' => ['array' => 0, 'callback' => 1], 'array_filter' => ['array' => 0, 'callback' => 1]];
}

<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

final class DemoFile
{
    /**
     * @phpstan-var array<self::VALUE*, string>
     */
    private static $availableValuesPhpstan = [
        self::VALUE_ON => '&#128161; veröffentlicht',
        self::VALUE_OFF => '&#10060; nicht veröffentlicht',
    ];

    /**
     * @psalm-var array<self::VALUE*, string>
     */
    private static $availableValuesPsalm = [
        self::VALUE_ON => '&#128161; veröffentlicht',
        self::VALUE_OFF => '&#10060; nicht veröffentlicht',
    ];
}

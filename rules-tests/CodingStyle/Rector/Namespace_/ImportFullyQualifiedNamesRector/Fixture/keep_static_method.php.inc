<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

abstract class KeepStaticMethod
{
    public static function decode(string $token): Contract\Token
    {
        $parts = explode('|', (string) base64_decode($token, true));

        return Token::create($parts[0] ?? '', $parts[1] ?? '');
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture\Contract\Token;
abstract class KeepStaticMethod
{
    public static function decode(string $token): Token
    {
        $parts = explode('|', (string) base64_decode($token, true));

        return Token::create($parts[0] ?? '', $parts[1] ?? '');
    }
}

?>

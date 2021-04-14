<?php
declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

/**
 * @Api\ApiResource(
 *     normalizationContext={"groups"={"default"}}
 * )
 */
final class QuotedKeysAndValues
{
}

?>
-----
PhpParser\Node\Stmt\Class_
-----
Api\ApiResource

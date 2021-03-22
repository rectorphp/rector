<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Util;

use DateTime;
use Iterator;
use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\TestCase;
use Rector\Core\Util\StaticNodeInstanceOf;
use stdClass;

final class StaticNodeInstanceOfTest extends TestCase
{
    /**
     * @dataProvider provideIsOneOf()
     * @param array<class-string<Node>> $array
     * @param DateTime|stdClass|null $object
     */
    public function testIsOneOf(?object $object, array $array, bool $expected): void
    {
        $this->assertSame($expected, StaticNodeInstanceOf::isOneOf($object, $array));
    }

    public function provideIsOneOf(): Iterator
    {
        yield [new String_('hey'), [LNumber::class, String_::class], true];
        yield [null, [Nop::class], false];
    }
}

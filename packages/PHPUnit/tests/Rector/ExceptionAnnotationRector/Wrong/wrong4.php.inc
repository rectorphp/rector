<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\TestCase;

class ExceptionMessageTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testLiteralMessage(): void
    {
        throw new \Exception('A literal exception message');
    }

    /**
     * @expectedException \Exception
     */
    public function testPartialMessageBegin(): void
    {
        throw new \Exception('A partial exception message');
    }
}

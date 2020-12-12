<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

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

?>
-----
<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Rector\PHPUnit\Tests\Rector\ClassMethod\ExceptionAnnotationRector\Fixture;

use PHPUnit\Framework\TestCase;

class ExceptionMessageTest extends TestCase
{
    public function testLiteralMessage(): void
    {
        $this->expectException(\Exception::class);
        throw new \Exception('A literal exception message');
    }

    public function testPartialMessageBegin(): void
    {
        $this->expectException(\Exception::class);
        throw new \Exception('A partial exception message');
    }
}

?>

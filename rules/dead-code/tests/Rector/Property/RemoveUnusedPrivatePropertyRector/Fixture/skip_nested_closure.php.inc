<?php
declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use stdClass;

class SkipNestedClosure
{
    private $mailer;
    private $message;

    public function setUp()
    {
        $this->message = new stdClass();
        $this->mailer = new stdClass();
    }

    public function test()
    {
        $this->mailer->method()
            ->with(
                $this->anything(),
                $this->callback(
                    function () {
                        $this->message
                            ->expects($this->once())
                            ->method()
                            ->with($this->a(), $this->b());

                        $this->call();

                        return true;
                    }
                )
            );
    }
}

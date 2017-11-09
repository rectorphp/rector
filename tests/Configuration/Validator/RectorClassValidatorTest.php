<?php declare(strict_types=1);

namespace Rector\Tests\Configuration\Validator;

use PHPUnit\Framework\TestCase;
use Rector\Configuration\Validator\RectorClassValidator;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\Validator\InvalidRectorClassException;
use stdClass;

final class RectorClassValidatorTest extends TestCase
{
    /**
     * @var RectorClassValidator
     */
    private $rectorClassValidator;

    protected function setUp(): void
    {
        $this->rectorClassValidator = new RectorClassValidator;
    }

    public function testNonExistingClass(): void
    {
        $nonExistingClass = 'NonExistingClass';

        $this->expectException(InvalidRectorClassException::class);
        $this->expectExceptionMessage(sprintf(
            'Rector "%s" was not found. Make sure class exists and is autoloaded.',
            $nonExistingClass
        ));

        $this->rectorClassValidator->validate([$nonExistingClass]);
    }

    public function testNonRectorClass(): void
    {
        $nonRectorClass = stdClass::class;

        $this->expectException(InvalidRectorClassException::class);
        $this->expectExceptionMessage(sprintf(
            'Rector "%s" is not supported. Use class that implements "%s".',
            $nonRectorClass,
            RectorInterface::class
        ));

        $this->rectorClassValidator->validate([$nonRectorClass]);
    }
}

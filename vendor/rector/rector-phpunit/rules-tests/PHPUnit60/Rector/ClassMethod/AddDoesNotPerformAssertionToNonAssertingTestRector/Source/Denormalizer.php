<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source;

final class Denormalizer
{
    /**
     * @var \Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\DenormalizerInterface
     */
    private $denormalizer;
    public function __construct(\Rector\PHPUnit\Tests\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source\DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }
    public function handle(array $data, string $type) : ?array
    {
        try {
            return $this->denormalizer->denormalize($data, $type);
        } catch (\Throwable $throwable) {
            return null;
        }
    }
}

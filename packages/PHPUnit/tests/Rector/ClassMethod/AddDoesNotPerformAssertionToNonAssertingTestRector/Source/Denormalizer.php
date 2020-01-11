<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector\Source;

final class Denormalizer
{
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    public function handle(array $data, string $type): ?array
    {
        try {
            return $this->denormalizer->denormalize($data, $type);
        } catch (\Throwable $throwable) {
            return null;
        }
    }
}

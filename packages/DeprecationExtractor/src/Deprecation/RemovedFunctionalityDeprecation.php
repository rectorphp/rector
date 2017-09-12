<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Deprecation;

use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;

final class RemovedFunctionalityDeprecation implements DeprecationInterface
{
    /**
     * @var string
     */
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}

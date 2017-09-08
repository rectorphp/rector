<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tranformer;

use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;

final class MessageToDeprecationTransformer
{
    public function transform(string $message): DeprecationInterface
    {
        dump($message);
        die;
    }
}

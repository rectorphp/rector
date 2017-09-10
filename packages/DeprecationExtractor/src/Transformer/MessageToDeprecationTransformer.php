<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Transformer;

use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;

final class MessageToDeprecationTransformer
{
    /**
     * @todo require some context
     */
    public function transform(string $message): DeprecationInterface
    {
        dump($message);
        die;
    }
}

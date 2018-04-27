<?php declare(strict_types=1);

namespace Rector\Bridge\Symfony;

use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;

final class DefaultServiceTypeForNameProvider implements ServiceTypeForNameProviderInterface
{
    /**
     * @var string[]
     */
    private $nameToTypeMap = [
        'some_service' => 'stdClass',
    ];

    public function provideTypeForName(string $name): ?string
    {
        // make this default, register and require kernel_class paramter, see:
        // https://github.com/rectorphp/rector/issues/428

        return $this->nameToTypeMap[$name] ?? null;
    }
}

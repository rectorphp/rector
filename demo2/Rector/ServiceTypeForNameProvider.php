<?php declare(strict_types=1);

namespace Demo2\Rector;

use Demo2\Bartender;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;

final class ServiceTypeForNameProvider implements ServiceTypeForNameProviderInterface
{
    /**
     * @var string[]
     *
     * @note Here local AppKernel would be used instead.
     */
    private $nameToTypeList = [
        'bartender' => Bartender::class
    ];

    public function provideTypeForName(string $name): ?string
    {
        return $this->nameToTypeList[$name] ?? null;
    }
}

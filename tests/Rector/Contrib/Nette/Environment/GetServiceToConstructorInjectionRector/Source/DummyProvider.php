<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\Environment\GetServiceToConstructorInjectionRector\Source;

use Rector\Contract\Bridge\ServiceNameToTypeProviderInterface;

final class DummyProvider implements ServiceNameToTypeProviderInterface
{
    /**
     * @return string[]
     */
    public function provide(): array
    {
        return [
            'someService' => 'someType',
        ];
    }
}

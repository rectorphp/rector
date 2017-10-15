<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\HttpKernel\GetterToPropertyRector\Source;

use Rector\Contract\Bridge\ServiceNameToTypeProviderInterface;

final class DummyProvider implements ServiceNameToTypeProviderInterface
{
    /**
     * @return string[]
     */
    public function provide(): array
    {
        return [
            'some_service' => 'stdClass',
        ];
    }
}

<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\Console\CommandToConstructorInjectionRector\Source;

use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;

final class DummyProvider implements ServiceTypeForNameProviderInterface
{
    /**
     * @var string[]
     */
    private $nameToTypeMap = [
        'some_service' => 'stdClass',
    ];

    public function provideTypeForName(string $name): ?string
    {
        return $this->nameToTypeMap[$name] ?? null;
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}
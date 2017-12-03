<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Rector\YamlParser\Contract\Rector\YamlRectorInterface;
use SplFileInfo;

final class AutoconfigureRector implements YamlRectorInterface
{
    /**
     * @var string[]
     */
    private $systemTags = [
        'auto_alias',
        'console.command',
        'controller.argument_value_resolver',
        'data_collector',
        'doctrine.event_listener',
        'doctrine.event_subscriber',
        'form.type',
        'form.type_extension',
        'form.type_guesser',
        'kernel.cache_clearer',
        'kernel.cache_warmer',
        'kernel.event_listener',
        'kernel.event_subscriber',
        'kernel.fragment_renderer',
        'monolog.logger',
        'monolog.processor',
        'routing.loader',
        'routing.expression_language_provider',
        'security.expression_language_provider',
        'security.voter',
        'security.remember_me_aware',
        'serializer.encoder',
        'serializer.normalizer',
        'swiftmailer.default.plugin',
        'templating.helper',
        'translation.loader',
        'translation.extractor',
        'translation.dumper',
        'twig.extension',
        'twig.loader',
        'validator.constraint_validator',
        'validator.initializer',
    ];

    /**
     * @var bool
     */
    private $shouldAutoconfigure = false;

    public function getCandidateKey(): string
    {
        return 'services';
    }

    /**
     * @param mixed[] $services
     * @return mixed[]
     */
    public function refactor(array $services, SplFileInfo $fileInfo): array
    {
        // skip if autoconfigure already exists
        if (isset($services['_defaults']['autoconfigure'])) {
            return $services;
        }

        // find class with system tags
        foreach ($services as $key => $service) {
            if (! isset($service['tags'])) {
                continue;
            }

            $tags = $service['tags'];
            // more tags or more than tag name
            if (count($tags) !== 1 || count($tags[0]) !== 1) {
                continue;
            }

            if (! isset($tags[0]['name'])) {
                continue;
            }

            // is system tag name
            $tagName = $tags[0]['name'];
            if (! in_array($tagName, $this->systemTags, true)) {
                continue;
            }

            $this->shouldAutoconfigure = true;

            // remove system tags
            unset($services[$key]['tags']);
        }

        if ($this->shouldAutoconfigure) {
            $services = $this->prependAutoconfigure($services);
        }


        return $services;
    }

    /**
     * @param mixed[] $services
     * @return mixed[]
     */
    private function prependAutoconfigure(array $services): array
    {
        $defaultsAutowire = [
            '_defaults' => [
                'autoconfigure' => true,
            ],
        ];

        return array_merge($defaultsAutowire, $services);
    }
}

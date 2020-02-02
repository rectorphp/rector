<?php

declare(strict_types=1);

namespace Rector\Symfony;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\Symfony\Contract\Tag\TagInterface;
use Rector\Symfony\Exception\XmlContainerNotExistsException;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\ServiceMap\ServiceMap;
use Rector\Symfony\ValueObject\Tag;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
use SimpleXMLElement;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

/**
 * Inspired by https://github.com/phpstan/phpstan-symfony/tree/master/src/Symfony
 */
final class ServiceMapProvider
{
    /**
     * @var string
     */
    private const SYMFONY_CONTAINER_XML_PATH_PARAMETER = 'symfony_container_xml_path';

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function provide(): ServiceMap
    {
        $symfonyContainerXmlPath = $this->getSymfonyContainerXmlPath();
        if ($symfonyContainerXmlPath === '') {
            return new ServiceMap([]);
        }

        $fileContents = FileSystem::read($symfonyContainerXmlPath);

        return $this->createServiceMapFromXml($fileContents);
    }

    private function getSymfonyContainerXmlPath(): string
    {
        return (string) $this->parameterProvider->provideParameter(self::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
    }

    private function createServiceMapFromXml(string $fileContents): ServiceMap
    {
        $xml = @simplexml_load_string($fileContents);

        if (! $xml) {
            throw new XmlContainerNotExistsException(sprintf(
                'Container %s cannot be parsed', $this->getSymfonyContainerXmlPath()
            ));
        }

        /** @var ServiceDefinition[] $services */
        $services = [];

        /** @var ServiceDefinition[] $aliases */
        $aliases = [];

        foreach ($xml->services->service as $def) {
            /** @var SimpleXMLElement $attrs */
            $attrs = $def->attributes();
            if (! isset($attrs->id)) {
                continue;
            }

            $def = $this->convertXmlToArray($def);
            $tags = $this->createTagFromXmlElement($def);

            $service = $this->createServiceFromXml($attrs, $tags);
            if ($service->getAlias() !== null) {
                $aliases[] = $service;
            } else {
                $services[$service->getId()] = $service;
            }
        }

        $services = $this->createAliasServiceDefinitions($aliases, $services);

        return new ServiceMap($services);
    }

    /**
     * @return mixed[]
     */
    private function convertXmlToArray(SimpleXMLElement $simpleXMLElement): array
    {
        $data = Json::decode(Json::encode((array) $simpleXMLElement), Json::FORCE_ARRAY);

        $data = $this->unWrapAttributes($data);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data = $this->convertedNestedArrayOrXml($value, $data, $key);
            } elseif ($value instanceof SimpleXMLElement) {
                $data[$key] = $this->convertXmlToArray($value);
            }
        }

        return $data;
    }

    private function createTagFromXmlElement($def): array
    {
        if (! isset($def['tag'])) {
            return [];
        }

        $tags = [];
        if (is_array($def['tag'])) {
            foreach ($def['tag'] as $tag) {
                $tags[] = $tag;
            }
        } else {
            $tags[] = $def['tag'];
        }

        return $tags;
    }

    /**
     * @param mixed[] $tags
     */
    private function createServiceFromXml(SimpleXMLElement $attrs, array $tags): ServiceDefinition
    {
        $tags = $this->createTagsFromXml($tags);

        return new ServiceDefinition(
            strpos((string) $attrs->id, '.') === 0 ? Strings::substring((string) $attrs->id, 1) : (string) $attrs->id,
            isset($attrs->class) ? (string) $attrs->class : null,
            ! isset($attrs->public) || (string) $attrs->public !== 'false',
            isset($attrs->synthetic) && (string) $attrs->synthetic === 'true',
            isset($attrs->alias) ? (string) $attrs->alias : null,
            $tags
        );
    }

    /**
     * @param ServiceDefinition[] $aliases
     * @param ServiceDefinition[] $services
     * @return ServiceDefinition[]
     */
    private function createAliasServiceDefinitions(array $aliases, array $services): array
    {
        foreach ($aliases as $service) {
            $alias = $service->getAlias();
            if ($alias === null) {
                continue;
            }

            if (! isset($services[$alias])) {
                continue;
            }

            $id = $service->getId();
            $services[$id] = new ServiceDefinition(
                $id,
                $services[$alias]->getClass(),
                $service->isPublic(),
                $service->isSynthetic(),
                $alias,
                []
            );
        }
        return $services;
    }

    private function unWrapAttributes(array $data): array
    {
        if (isset($data['@attributes'])) {
            foreach ($data['@attributes'] as $key => $value) {
                $data[$key] = $value;
            }

            unset($data['@attributes']);
        }

        return $data;
    }

    private function convertedNestedArrayOrXml(array $value, array $data, $key): array
    {
        foreach ($value as $subKey => $subValue) {
            if ($subValue instanceof SimpleXMLElement) {
                $data[$key][$subKey] = $this->convertXmlToArray($subValue);
            } elseif (is_array($subValue)) {
                $data[$key][$subKey] = $this->unWrapAttributes($subValue);
            }
        }

        return $data;
    }

    /**
     * @param mixed[] $tags
     * @return TagInterface[]
     */
    private function createTagsFromXml(array $tags): array
    {
        $tagValueObjects = [];
        foreach ($tags as $key => $tag) {
            $data = $tag;
            $name = $data['name'] ?? '';

            if ($name === 'kernel.event_listener') {
                $tagValueObjects[$key] = new EventListenerTag(
                    $data['event'] ?? '',
                    $data['method'] ?? '',
                    (int) ($data['priority'] ?? 0)
                );
            } else {
                unset($data['name']);
                $tagValueObjects[$key] = new Tag($name, $data);
            }
        }

        return $tagValueObjects;
    }
}

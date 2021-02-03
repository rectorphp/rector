<?php

declare(strict_types=1);

namespace Rector\Symfony;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\Symfony\Exception\XmlContainerNotExistsException;
use Rector\Symfony\ValueObject\ServiceDefinition;
use Rector\Symfony\ValueObject\ServiceMap\ServiceMap;
use Rector\Symfony\ValueObject\Tag;
use Rector\Symfony\ValueObject\Tag\EventListenerTag;
use SimpleXMLElement;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileSystem;

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
     * @var string
     */
    private const TAG = 'tag';

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(ParameterProvider $parameterProvider, SmartFileSystem $smartFileSystem)
    {
        $this->parameterProvider = $parameterProvider;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function provide(): ServiceMap
    {
        $symfonyContainerXmlPath = $this->getSymfonyContainerXmlPath();
        if ($symfonyContainerXmlPath === '') {
            return new ServiceMap([]);
        }

        $fileContents = $this->smartFileSystem->readFile($symfonyContainerXmlPath);
        return $this->createServiceMapFromXml($fileContents);
    }

    private function getSymfonyContainerXmlPath(): string
    {
        return (string) $this->parameterProvider->provideParameter(self::SYMFONY_CONTAINER_XML_PATH_PARAMETER);
    }

    private function createServiceMapFromXml(string $fileContents): ServiceMap
    {
        // "@" intentionally
        $xml = @simplexml_load_string($fileContents);
        if (! $xml) {
            throw new XmlContainerNotExistsException(sprintf(
                'Container "%s" cannot be parsed', $this->getSymfonyContainerXmlPath()
            ));
        }

        /** @var ServiceDefinition[] $services */
        $services = [];

        /** @var ServiceDefinition[] $aliases */
        $aliases = [];

        foreach ($xml->services->service as $def) {
            /** @var SimpleXMLElement $attrs */
            $attrs = $def->attributes();
            if (! (property_exists($attrs, 'id') && $attrs->id !== null)) {
                continue;
            }

            $def = $this->convertXmlToArray($def);
            $tags = $this->createTagFromXmlElement($def);

            $service = $this->createServiceFromXmlAndTagsData($attrs, $tags);
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

    /**
     * @param mixed[] $def
     * @return mixed[]
     */
    private function createTagFromXmlElement(array $def): array
    {
        if (! isset($def[self::TAG])) {
            return [];
        }

        $tags = [];
        if (is_array($def[self::TAG])) {
            foreach ($def[self::TAG] as $tag) {
                $tags[] = $tag;
            }
        } else {
            $tags[] = $def[self::TAG];
        }

        return $tags;
    }

    /**
     * @param mixed[] $tags
     */
    private function createServiceFromXmlAndTagsData(SimpleXMLElement $attrs, array $tags): ServiceDefinition
    {
        $tags = $this->createTagsFromData($tags);

        return new ServiceDefinition(
            strpos((string) $attrs->id, '.') === 0 ? Strings::substring((string) $attrs->id, 1) : (string) $attrs->id,
            property_exists($attrs, 'class') && $attrs->class !== null ? (string) $attrs->class : null,
            ! (property_exists($attrs, 'public') && $attrs->public !== null) || (string) $attrs->public !== 'false',
            property_exists($attrs, 'synthetic') && $attrs->synthetic !== null && (string) $attrs->synthetic === 'true',
            property_exists($attrs, 'alias') && $attrs->alias !== null ? (string) $attrs->alias : null,
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

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
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

    /**
     * @param mixed[] $value
     * @param mixed[] $data
     * @param string|int $key
     * @return mixed[]
     */
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
     * @param mixed[] $tagsData
     * @return Tag[]|EventListenerTag[]
     */
    private function createTagsFromData(array $tagsData): array
    {
        $tagValueObjects = [];
        foreach ($tagsData as $key => $tag) {
            if (is_string($tag)) {
                $tagValueObjects[$key] = new Tag($tag);
                continue;
            }

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
                $tagValueObjects[$key] = new Tag($name, $data ?? []);
            }
        }

        return $tagValueObjects;
    }
}

<?php declare(strict_types=1);

namespace Rector\YamlParser\Tests;

use PHPUnit\Framework\TestCase;
use Rector\YamlParser\YamlParser;
use Symfony\Component\Yaml\Yaml;

final class YamlParserTest extends TestCase
{
    /**
     * @var YamlParser
     */
    private $yamlParser;

    protected function setUp(): void
    {
        $this->yamlParser = new YamlParser();
    }

    public function test(): void
    {
        $file = __DIR__ . '/YamlParserSource/some_services.yml';

        $result = $this->yamlParser->parseFile($file);

        // 1. yaml to php
        // 2. parse the php with php-parser
        // use rector and back to yaml

        // change it
        $services = $result->getData()['services'];

        $newServices = [];
        foreach ($services as $name => $service) {
            if ($name === $service['class'] || (is_string($name) && $service['class'])) {
                unset($services[$name]);
                $newServices[$service['class']] = '~';
            }
        }

        $data = $result->getData();
        $data['services'] = $newServices;

        $result = Yaml::dump($data);

        $this->assertStringEqualsFile(
            __DIR__ . '/YamlParserSource/expected.some_services.yml',
            $result
        );
    }
}

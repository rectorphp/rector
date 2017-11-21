<?php declare(strict_types=1);

namespace Rector\YamlParser;

use PHPUnit\Framework\TestCase;

final class YamlParserTest extends TestCase
{
    /**
     * @var YamlParser
     */
    private $yamlParser;

    protected function setUp()
    {
        $this->yamlParser = new YamlParser();
    }

    public function test(): void
    {
        $result = $this->yamlParser->parseFile(__DIR__ . '/YamlParserSource/some_services.yml');

        dump($result);
        $result = implode(' ', $result);

        $this->assertStringEqualsFile(
            __DIR__ . '/YamlParserSource/expected.some_services.yml',
            $result
        );
    }
}

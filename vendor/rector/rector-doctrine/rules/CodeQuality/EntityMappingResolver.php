<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality;

use PHPStan\Reflection\ReflectionProvider;
use Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use RectorPrefix202506\Symfony\Component\Finder\Finder;
use RectorPrefix202506\Symfony\Component\Finder\SplFileInfo;
use RectorPrefix202506\Symfony\Component\Yaml\Yaml;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class EntityMappingResolver
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var EntityMapping[]
     */
    private array $entityMappings = [];
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param string[] $yamlMappingDirectories
     * @return EntityMapping[]
     */
    public function resolveFromDirectories(array $yamlMappingDirectories) : array
    {
        Assert::allString($yamlMappingDirectories);
        if ($this->entityMappings !== []) {
            return $this->entityMappings;
        }
        $yamlFileInfos = $this->findYamlFileInfos($yamlMappingDirectories);
        Assert::notEmpty($yamlFileInfos);
        $this->entityMappings = $this->createEntityMappingsFromYamlFileInfos($yamlFileInfos);
        Assert::notEmpty($this->entityMappings);
        return $this->entityMappings;
    }
    /**
     * @param string[] $yamlMappingDirectories
     * @return SplFileInfo[]
     */
    private function findYamlFileInfos(array $yamlMappingDirectories) : array
    {
        Assert::notEmpty($yamlMappingDirectories);
        Assert::allString($yamlMappingDirectories);
        Assert::allFileExists($yamlMappingDirectories);
        $finder = new Finder();
        $finder->files()->name('#\\.(yml|yaml)$#')->in($yamlMappingDirectories)->notPath('DataFixtures')->getIterator();
        return \iterator_to_array($finder->getIterator());
    }
    /**
     * @param SplFileInfo[] $yamlFileInfos
     * @return EntityMapping[]
     */
    private function createEntityMappingsFromYamlFileInfos(array $yamlFileInfos) : array
    {
        Assert::allIsInstanceOf($yamlFileInfos, SplFileInfo::class);
        $entityMappings = [];
        foreach ($yamlFileInfos as $yamlFileInfo) {
            // is a mapping file?
            $yaml = Yaml::parse($yamlFileInfo->getContents());
            foreach ($yaml as $key => $value) {
                // for tests
                if (!$this->reflectionProvider->hasClass($key) && \strpos((string) $key, 'Rector\\Doctrine\\Tests\\CodeQuality') === \false) {
                    continue;
                }
                $entityMappings[] = new EntityMapping($key, $value);
            }
        }
        return $entityMappings;
    }
}

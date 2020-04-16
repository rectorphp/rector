<?php

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

final class ClassChangesDiffExtractor
{
    /**
     * @see https://regex101.com/r/tyABnc/1/
     * @var string
     */
    private const BEFORE_AFTER_PATTERN = '#^\-(?<before>[^-].*?)$\n\+(?<after>.*?)$#ms';

    /**
     * @var string
     */
    private const CLASS_NAME_PREFIX = 'Doctrine';

    /**
     * @see https://regex101.com/r/tyABnc/2/
     * @var string
     */
    private const CLASS_NAME_PATTERN = '#(?<class_name>' . self::CLASS_NAME_PREFIX . '\\\\[\w|\\\\]+)#';

    /**
     * @var string[]
     */
    private $classesBeforeAfter = [];

    public function run(string $diffFilePath): void
    {
        $diff = FileSystem::read($diffFilePath);
        $beforeAfterMatches = Strings::matchAll($diff, self::BEFORE_AFTER_PATTERN);

        $classesBeforeAfter = [];
        foreach ($beforeAfterMatches as $beforeAfterMatch) {
            $classBeforeAndAfter = $this->resolveClassBeforeAndAfter($beforeAfterMatch);
            if ($classBeforeAndAfter === null) {
                continue;
            }

            [$classBefore, $classAfter] = $classBeforeAndAfter;

            if (Strings::contains($classBefore, 'Tests')) {
                continue;
            }

            // classes are the same, no change in the class name
            if ($classBefore === $classAfter) {
                continue;
            }

            $this->classesBeforeAfter[$classBefore] = $classAfter;
        }

        $this->report();
    }

    private function report(): void
    {
        $this->classesBeforeAfter = array_unique($this->classesBeforeAfter);
        ksort($this->classesBeforeAfter);

        $yaml = Yaml::dump($this->classesBeforeAfter);
        echo PHP_EOL;
        echo $yaml;
        echo PHP_EOL;
    }

    /**
     * @return string[]|null
     */
    private function resolveClassBeforeAndAfter(array $beforeAfterMatch): ?array
    {
        // file change
        if (Strings::contains($beforeAfterMatch['before'], '//')) {
            return null;
        }

        $classNameBefore = Strings::match($beforeAfterMatch['before'], self::CLASS_NAME_PATTERN);
        if ($classNameBefore === null) {
            return null;
        }

        $classNameAfter = Strings::match($beforeAfterMatch['after'], self::CLASS_NAME_PATTERN);
        if ($classNameAfter === null) {
            return null;
        }

        return [$classNameBefore['class_name'], $classNameAfter['class_name']];
    }
}

$classChangesDiffExtractor = new ClassChangesDiffExtractor();
// $classChangesDiffExtractor->run();

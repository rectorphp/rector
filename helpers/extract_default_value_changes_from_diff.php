<?php

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use Symfony\Component\Yaml\Yaml;


require __DIR__ . '/../vendor/autoload.php';

/**
 * @potential change to command
 */
final class RemovedDefaultValueDiffExtractor
{
    /**
     * @see https://regex101.com/r/pe3DNc/1/
     * @var string
     */
    private const FILE_PATTERN = '#^--- a\/src\/(?<file_name>(.*?))\.php$#ms';

    /**
     * @see https://regex101.com/r/pe3DNc/1/
     * @var string
     */
    private const REMOVED_DEFAULT_VALUE_PATTERN = '#^-(.*?)function\s(?<method_name>\w+)(.*?)=\s?(?<value>.*?)\)$#ms';

    /**
     * @var string|null
     */
    private $currentClass;

    /**
     * @var mixed[]
     */
    private $collectedChanges = [];

    /**
     * @var string[]
     */
    private $newToOldClasses = [];

    /**
     * @var string[]
     */
    private const CLASSES_TO_SKIP = [
        'PhpOffice\PhpSpreadsheet\Shared\JAMA\utils\Error',
        'PhpOffice\PhpSpreadsheet\Worksheet\Dimension',
    ];

    public function __construct()
    {
        $yaml = Yaml::parseFile(__DIR__ .  '/../config/set/phpoffice/phpexcel-to-phpspreadsheet.yaml');
        $oldToNewClasses = $yaml['services']['Rector\Renaming\Rector\Class_\RenameClassRector']['$oldToNewClasses'];
        $this->newToOldClasses = array_flip($oldToNewClasses);

        // extra map - @todo possibly check class name changes and add to config
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\CalcEngine\Logger'] = 'PHPExcel_CalcEngine_Logger';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Calculation'] = 'PHPExcel_Calculation';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Cell'] = 'PHPExcel_Cell';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Chart'] = 'PHPExcel_Chart';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\RichText'] = 'PHPExcel_RichText';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Style'] = 'PHPExcel_Style';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Worksheet'] = 'PHPExcel_Worksheet';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Writer\Pdf\Core'] = 'PHPExcel_Writer_PDF_Core';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Writer\Pdf\DomPDF'] = 'PHPExcel_Writer_PDF_DomPDF';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Writer\Pdf\MPDF'] = 'PHPExcel_Writer_PDF_mPDF';
        $this->newToOldClasses['PhpOffice\PhpSpreadsheet\Writer\Pdf\TcPDF'] = 'PHPExcel_Writer_PDF_tcPDF';
    }

    public function run(string $diffFilepath)
    {
        $lineByLineContent = $this->readFileToLines($diffFilepath);

        foreach ($lineByLineContent as $lineContent) {
            $this->detectCurrentClass($lineContent);

            if (in_array($this->currentClass, self::CLASSES_TO_SKIP, true)) {
                continue;
            }

            $matches = Strings::match($lineContent, self::REMOVED_DEFAULT_VALUE_PATTERN);
            if (! $matches) {
                continue;
            }

            // match args
            $match = Strings::match($lineContent, '#\((?<parameters>.*?)\)#');
            if(!isset($match['parameters'])) {
                throw new ShouldNotHappenException();
            }

            $methodName = $matches['method_name'];
            $value = $matches['value'];

            $parameters = Strings::split($match['parameters'], '#,\s+#');
            foreach ($parameters as $position => $parameterContent) {
                $match = Strings::match($parameterContent, '#=\s+(?<default_value>.*?)$#');
                if ($match === null) {
                    dump($lineContent);
                    dump($parameterContent);
                    continue;
                }

                $value = $match['default_value'];
                if ($value === 'null') {
                    $value = null;
                }

                if ($value === 'false') {
                    $value = false;
                }

                if ($value === 'true') {
                    $value = true;
                }

                $this->collectedChanges[$this->currentClass][$methodName][$position] = $value;
            }
        }

        $this->reportCollectedChanges();
    }

    private function detectCurrentClass(string $fileContent): void
    {
        $matches = Strings::match($fileContent, self::FILE_PATTERN);
        if (! $matches) {
            return;
        }

        // turn file into class
        $class = Strings::replace($matches['file_name'], '#/#', '\\');
        $newClass = 'PhpOffice\\' . $class;

        $isClassSkipped = in_array($newClass, self::CLASSES_TO_SKIP, true);
        if(! $isClassSkipped && ! isset($this->newToOldClasses[$newClass])) {
            throw new ShouldNotHappenException(sprintf('Could not find old class for "%s"', $newClass));
        }

        $oldClass = $this->newToOldClasses[$newClass] ?? $newClass;
        $this->currentClass = $oldClass;
    }

    /**
     * @return string[]
     */
    private function readFileToLines(string $diffFilepath): array
    {
        $fileContent = FileSystem::read($diffFilepath);
        return explode(PHP_EOL, $fileContent);
    }

    private function reportCollectedChanges(): void
    {
        ksort($this->collectedChanges);

        $output = Json::encode($this->collectedChanges, Json::PRETTY);
        echo PHP_EOL;
        echo $output;
        echo PHP_EOL;

        FileSystem::write('output.json', $output);
    }
}

$removedDefaultValueDiffExtractor = new RemovedDefaultValueDiffExtractor();
$removedDefaultValueDiffExtractor->run(__DIR__ . '/../workhouse/some.diff');

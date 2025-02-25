<?php

/**
 * JUnit specification:
 * - https://github.com/junit-team/junit5/blob/main/platform-tests/src/test/resources/jenkins-junit.xsda
 */
declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use DOMDocument;
use DOMElement;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\ProcessResult;
use RectorPrefix202502\Symfony\Component\Console\Style\SymfonyStyle;
final class JUnitOutputFormatter implements OutputFormatterInterface
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    public const NAME = 'junit';
    private const XML_ATTRIBUTE_FILE = 'file';
    private const XML_ATTRIBUTE_NAME = 'name';
    private const XML_ATTRIBUTE_TYPE = 'type';
    private const XML_ELEMENT_TESTSUITES = 'testsuites';
    private const XML_ELEMENT_TESTSUITE = 'testsuite';
    private const XML_ELEMENT_TESTCASE = 'testcase';
    private const XML_ELEMENT_ERROR = 'error';
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function getName() : string
    {
        return self::NAME;
    }
    public function report(ProcessResult $processResult, Configuration $configuration) : void
    {
        if (!\extension_loaded('dom')) {
            $this->symfonyStyle->warning('The "dom" extension is not loaded. The rector could not generate a response in the JUnit format');
            return;
        }
        $domDocument = new DOMDocument('1.0', 'UTF-8');
        $xmlTestSuite = $domDocument->createElement(self::XML_ELEMENT_TESTSUITE);
        $xmlTestSuite->setAttribute(self::XML_ATTRIBUTE_NAME, 'rector');
        $xmlTestSuites = $domDocument->createElement(self::XML_ELEMENT_TESTSUITES);
        $xmlTestSuites->appendChild($xmlTestSuite);
        $domDocument->appendChild($xmlTestSuites);
        $this->appendSystemErrors($processResult, $configuration, $domDocument, $xmlTestSuite);
        $this->appendFileDiffs($processResult, $configuration, $domDocument, $xmlTestSuite);
        echo $domDocument->saveXML() . \PHP_EOL;
    }
    private function appendSystemErrors(ProcessResult $processResult, Configuration $configuration, DOMDocument $domDocument, DOMElement $xmlTestSuite) : void
    {
        if (\count($processResult->getSystemErrors()) === 0) {
            return;
        }
        foreach ($processResult->getSystemErrors() as $error) {
            $filePath = $configuration->isReportingWithRealPath() ? $error->getAbsoluteFilePath() ?? '' : $error->getRelativeFilePath() ?? '';
            $xmlError = $domDocument->createElement(self::XML_ELEMENT_ERROR, $error->getMessage());
            $xmlError->setAttribute(self::XML_ATTRIBUTE_TYPE, 'Error');
            $xmlTestCase = $domDocument->createElement(self::XML_ELEMENT_TESTCASE);
            $xmlTestCase->setAttribute(self::XML_ATTRIBUTE_FILE, $filePath);
            $xmlTestCase->setAttribute(self::XML_ATTRIBUTE_NAME, $filePath . ':' . $error->getLine());
            $xmlTestCase->appendChild($xmlError);
            $xmlTestSuite->appendChild($xmlTestCase);
        }
    }
    private function appendFileDiffs(ProcessResult $processResult, Configuration $configuration, DOMDocument $domDocument, DOMElement $xmlTestSuite) : void
    {
        if (\count($processResult->getFileDiffs()) === 0) {
            return;
        }
        $fileDiffs = $processResult->getFileDiffs();
        \ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $filePath = $configuration->isReportingWithRealPath() ? $fileDiff->getAbsoluteFilePath() ?? '' : $fileDiff->getRelativeFilePath() ?? '';
            $rectorClasses = \implode(' / ', $fileDiff->getRectorShortClasses());
            $xmlError = $domDocument->createElement(self::XML_ELEMENT_ERROR, $fileDiff->getDiff());
            $xmlError->setAttribute(self::XML_ATTRIBUTE_TYPE, $rectorClasses);
            $xmlTestCase = $domDocument->createElement(self::XML_ELEMENT_TESTCASE);
            $xmlTestCase->setAttribute(self::XML_ATTRIBUTE_FILE, $filePath);
            $xmlTestCase->setAttribute(self::XML_ATTRIBUTE_NAME, $filePath . ':' . $fileDiff->getFirstLineNumber());
            $xmlTestCase->appendChild($xmlError);
            $xmlTestSuite->appendChild($xmlTestCase);
        }
    }
}

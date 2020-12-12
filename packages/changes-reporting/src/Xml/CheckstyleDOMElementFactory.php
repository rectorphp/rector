<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Xml;

use DOMDocument;
use DOMElement;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\ChangesReporting\ValueObject\RectorWithFileAndLineChange;
use Rector\Core\ValueObject\Reporting\FileDiff;

/**
 * Inspiration in @see \Symfony\Component\Console\Descriptor\XmlDescriptor
 */
final class CheckstyleDOMElementFactory
{
    /**
     * @var string
     */
    private const CHECKSTYLE = 'checkstyle';

    /**
     * @var string
     */
    private const FILE = 'file';

    /**
     * @var string
     */
    private const ERROR = 'error';

    public function create(DOMDocument $domDocument, ErrorAndDiffCollector $errorAndDiffCollector): DOMElement
    {
        $checkstyleDOMElement = $domDocument->createElement(self::CHECKSTYLE);

        foreach ($errorAndDiffCollector->getFileDiffs() as $fileDiff) {
            $fileDOMElement = $this->createFileDOMElement($domDocument, $fileDiff);
            $checkstyleDOMElement->appendChild($fileDOMElement);
        }

        $nonFileErrorDOMElement = $this->createNonFileErrorDOMElements($domDocument, $errorAndDiffCollector);
        if ($nonFileErrorDOMElement !== null) {
            $checkstyleDOMElement->appendChild($nonFileErrorDOMElement);
        }

        return $checkstyleDOMElement;
    }

    private function createFileDOMElement(DOMDocument $domDocument, FileDiff $fileDiff): DOMElement
    {
        $fileDOMElement = $domDocument->createElement(self::FILE);
        $fileDOMElement->setAttribute('name', $this->escapeForXml($fileDiff->getRelativeFilePath()));

        foreach ($fileDiff->getRectorChanges() as $rectorWithFileAndLineChange) {
            $errorDOMElement = $this->createErrorDOMElement($rectorWithFileAndLineChange, $domDocument);
            $fileDOMElement->appendChild($errorDOMElement);
        }

        return $fileDOMElement;
    }

    private function createNonFileErrorDOMElements(
        DOMDocument $domDocument,
        ErrorAndDiffCollector $errorAndDiffCollector
    ): ?DOMElement {
        if ($errorAndDiffCollector->getErrors() === []) {
            return null;
        }

        $fileDOMElement = $domDocument->createElement(self::FILE);

        foreach ($errorAndDiffCollector->getErrors() as $rectorError) {
            $errorDOMElement = $domDocument->createElement(self::ERROR);
            $errorDOMElement->setAttribute('severity', self::ERROR);
            $errorDOMElement->setAttribute('message', $this->escapeForXml($rectorError->getMessage()));

            $fileDOMElement->appendChild($errorDOMElement);
        }

        return $fileDOMElement;
    }

    private function escapeForXml(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT);
    }

    private function createErrorDOMElement(
        RectorWithFileAndLineChange $rectorWithFileAndLineChange,
        DOMDocument $domDocument
    ): DOMElement {
        $errorDOMElement = $domDocument->createElement(self::ERROR);

        $errorDOMElement->setAttribute('line', $this->escapeForXml((string) $rectorWithFileAndLineChange->getLine()));
        $errorDOMElement->setAttribute('column', '1');
        $errorDOMElement->setAttribute('severity', self::ERROR);

        $message = $rectorWithFileAndLineChange->getRectorDefinitionsDescription() . ' (Reported by: ' . $rectorWithFileAndLineChange->getRectorClass() . ')';
        $errorDOMElement->setAttribute('message', $this->escapeForXml($message));

        return $errorDOMElement;
    }
}

<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Output;

use DOMDocument;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\ChangesReporting\Xml\CheckstyleDOMElementFactory;

/**
 * Inspired by https://github.com/phpstan/phpstan-src/commit/fa1f416981438b80e2f39eabd9f1b62fca9a6803#diff-7a7d635d9f9cf3388e34d414731dece3
 */
final class CheckstyleOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'checkstyle';

    /**
     * @var CheckstyleDOMElementFactory
     */
    private $checkstyleDOMElementFactory;

    public function __construct(CheckstyleDOMElementFactory $checkstyleDOMElementFactory)
    {
        $this->checkstyleDOMElementFactory = $checkstyleDOMElementFactory;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function report(ErrorAndDiffCollector $errorAndDiffCollector): void
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');

        $domElement = $this->checkstyleDOMElementFactory->create($domDocument, $errorAndDiffCollector);
        $domDocument->appendChild($domElement);

        // pretty print with spaces
        $domDocument->formatOutput = true;
        echo $domDocument->saveXML();
    }
}

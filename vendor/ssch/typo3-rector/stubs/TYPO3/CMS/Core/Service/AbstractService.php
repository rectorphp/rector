<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Service;

if (\class_exists('TYPO3\\CMS\\Core\\Service\\AbstractService')) {
    return;
}
class AbstractService
{
    // General error - something went wrong
    const ERROR_GENERAL = -1;
    // During execution it showed that the service is not available and
    // should be ignored. The service itself should call $this->setNonAvailable()
    const ERROR_SERVICE_NOT_AVAILABLE = -2;
    // Passed subtype is not possible with this service
    const ERROR_WRONG_SUBTYPE = -3;
    // Passed subtype is not possible with this service
    const ERROR_NO_INPUT = -4;
    // File not found which the service should process
    const ERROR_FILE_NOT_FOUND = -20;
    // File not readable
    const ERROR_FILE_NOT_READABLE = -21;
    // File not writable
    // @todo: check writeable vs. writable
    const ERROR_FILE_NOT_WRITEABLE = -22;
    // Passed subtype is not possible with this service
    const ERROR_PROGRAM_NOT_FOUND = -40;
    // Passed subtype is not possible with this service
    const ERROR_PROGRAM_FAILED = -41;
}

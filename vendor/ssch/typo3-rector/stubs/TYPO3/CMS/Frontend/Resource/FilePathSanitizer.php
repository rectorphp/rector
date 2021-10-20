<?php

namespace RectorPrefix20211020\TYPO3\CMS\Frontend\Resource;

use RectorPrefix20211020\TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use RectorPrefix20211020\TYPO3\CMS\Core\Resource\Exception\InvalidFileException;
use RectorPrefix20211020\TYPO3\CMS\Core\Resource\Exception\InvalidFileNameException;
use RectorPrefix20211020\TYPO3\CMS\Core\Resource\Exception\InvalidPathException;
if (\class_exists('TYPO3\\CMS\\Frontend\\Resource\\FilePathSanitizer')) {
    return;
}
class FilePathSanitizer
{
    /**
     * @param string $originalFileName
     * @return string
     */
    public function sanitize($originalFileName)
    {
        $originalFileName = (string) $originalFileName;
        if ($originalFileName === 'foo') {
            throw new \RectorPrefix20211020\TYPO3\CMS\Core\Resource\Exception\InvalidFileNameException($originalFileName);
        }
        if ($originalFileName === 'bar') {
            throw new \RectorPrefix20211020\TYPO3\CMS\Core\Resource\Exception\InvalidPathException($originalFileName);
        }
        if ($originalFileName === 'baz') {
            throw new \RectorPrefix20211020\TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException($originalFileName);
        }
        if ($originalFileName === 'bazbar') {
            throw new \RectorPrefix20211020\TYPO3\CMS\Core\Resource\Exception\InvalidFileException($originalFileName);
        }
        return 'foo';
    }
}

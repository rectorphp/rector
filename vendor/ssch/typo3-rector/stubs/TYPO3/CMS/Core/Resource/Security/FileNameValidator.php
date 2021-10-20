<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Resource\Security;

if (\class_exists('TYPO3\\CMS\\Core\\Resource\\Security\\FileNameValidator')) {
    return;
}
class FileNameValidator
{
    /**
     * Previously this was used within SystemEnvironmentBuilder
     */
    const DEFAULT_FILE_DENY_PATTERN = '\\.(php[3-8]?|phpsh|phtml|pht|phar|shtml|cgi)(\\..*)?$|\\.pl$|^\\.htaccess$';
}

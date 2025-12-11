<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
return static function (RectorConfig $rectorConfig): void {
    // Symfony 6.2: Email::VALIDATION_MODE_LOOSE deprecated, use Email::VALIDATION_MODE_HTML5 instead
    // @see https://github.com/symfony/validator/blob/6.2/Constraints/Email.php
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassConstFetch('Symfony\Component\Validator\Constraints\Email', 'VALIDATION_MODE_LOOSE', 'VALIDATION_MODE_HTML5')]);
};

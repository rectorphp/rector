<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Node\Stmt;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\Parser\PathRoutingParser;

/**
 * @api Used in PHPStan internals for parsing nodes:
 * 1) with types for tests:
 * 2) removing unsupported PHP-version code on real run
 */
final class RectorPathRoutingParser extends PathRoutingParser
{
    private readonly Parser $currentPhpVersionRichParser;

    public function __construct(
        FileHelper $fileHelper,
        Parser $currentPhpVersionRichParser,
        Parser $currentPhpVersionSimpleParser,
        Parser $php8Parser
    ) {
        $this->currentPhpVersionRichParser = $currentPhpVersionRichParser;
        parent::__construct($fileHelper, $currentPhpVersionRichParser, $currentPhpVersionSimpleParser, $php8Parser);
    }

    /**
     * @return Stmt[]
     */
    public function parseFile(string $file): array
    {
        // for tests, always parse nodes with directly rich parser to be aware of types
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            return $this->currentPhpVersionRichParser->parseFile($file);
        }

        return parent::parseFile($file);
    }
}

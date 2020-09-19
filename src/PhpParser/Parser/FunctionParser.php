<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use ReflectionFunction;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FunctionParser
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(Parser $parser, SmartFileSystem $smartFileSystem)
    {
        $this->parser = $parser;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function parseFunction(ReflectionFunction $reflectionFunction): ?Namespace_
    {
        $fileName = $reflectionFunction->getFileName();
        if (! is_string($fileName)) {
            return null;
        }

        $functionCode = $this->smartFileSystem->readFile($fileName);
        if (! is_string($functionCode)) {
            return null;
        }

        $ast = $this->parser->parse($functionCode)[0];

        if (! $ast instanceof Namespace_) {
            return null;
        }

        return $ast;
    }
}

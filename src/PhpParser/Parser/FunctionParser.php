<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use Nette\Utils\FileSystem;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Parser;
use ReflectionFunction;

final class FunctionParser
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function parseFunction(ReflectionFunction $reflectionFunction): ?Namespace_
    {
        $fileName = $reflectionFunction->getFileName();
        if (! is_string($fileName)) {
            return null;
        }

        $functionCode = FileSystem::read($fileName);
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

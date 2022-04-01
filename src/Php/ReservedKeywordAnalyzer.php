<?php

declare (strict_types=1);
namespace Rector\Core\Php;

final class ReservedKeywordAnalyzer
{
    /**
     * @see https://www.php.net/manual/en/reserved.keywords.php
     * @var string[]
     */
    private const RESERVED_KEYWORDS = ['__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor', 'yield', 'yield from'];
    /**
     * @var string[]
     */
    private const NATIVE_VARIABLE_NAMES = ['_ENV', '_POST', '_GET', '_COOKIE', '_SERVER', '_FILES', '_REQUEST', '_SESSION', 'GLOBALS'];
    public function isNativeVariable(string $name) : bool
    {
        return \in_array($name, self::NATIVE_VARIABLE_NAMES, \true);
    }
    public function isReserved(string $keyword) : bool
    {
        $keyword = \strtolower($keyword);
        return \in_array($keyword, self::RESERVED_KEYWORDS, \true);
    }
}

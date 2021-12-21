<?php

declare (strict_types=1);
namespace RectorPrefix20211221\Helmich\TypoScriptParser\Tokenizer\Printer;

use RectorPrefix20211221\Helmich\TypoScriptParser\Tokenizer\TokenInterface;
use RectorPrefix20211221\Symfony\Component\Yaml\Yaml;
class StructuredTokenPrinter implements \RectorPrefix20211221\Helmich\TypoScriptParser\Tokenizer\Printer\TokenPrinterInterface
{
    /** @var Yaml */
    private $yaml;
    public function __construct(\RectorPrefix20211221\Symfony\Component\Yaml\Yaml $yaml = null)
    {
        $this->yaml = $yaml ?: new \RectorPrefix20211221\Symfony\Component\Yaml\Yaml();
    }
    /**
     * @param TokenInterface[] $tokens
     * @return string
     */
    public function printTokenStream(array $tokens) : string
    {
        $content = '';
        foreach ($tokens as $token) {
            $content .= \sprintf("%20s %s\n", $token->getType(), $this->yaml->dump($token->getValue()));
        }
        return $content;
    }
}

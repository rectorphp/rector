<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\Configuration\CurrentNodeProvider;
use Rector\DoctrinePhpDocParser\AnnotationReader\NodeAnnotationReader;

abstract class AbstractPhpDocParser
{
    /**
     * @var NodeAnnotationReader
     */
    protected $nodeAnnotationReader;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @required
     */
    public function autowireAbstractPhpDocParser(
        CurrentNodeProvider $currentNodeProvider,
        NodeAnnotationReader $nodeAnnotationReader
    ): void {
        $this->currentNodeProvider = $currentNodeProvider;
        $this->nodeAnnotationReader = $nodeAnnotationReader;
    }

    protected function getCurrentPhpNode(): Node
    {
        return $this->currentNodeProvider->getNode();
    }

    /**
     * Skip all tokens for this annotation, so next annotation can work with tokens after this one
     * Inspired at @see \PHPStan\PhpDocParser\Parser\PhpDocParser::parseText()
     */
    protected function resolveAnnotationContent(TokenIterator $tokenIterator): string
    {
        $annotationContent = '';
        $unclosedOpenedBracketCount = 0;
        while (true) {
            if ($tokenIterator->currentTokenType() === Lexer::TOKEN_OPEN_PARENTHESES) {
                ++$unclosedOpenedBracketCount;
            }

            if ($tokenIterator->currentTokenType() === Lexer::TOKEN_CLOSE_PARENTHESES) {
                --$unclosedOpenedBracketCount;
            }

            if ($unclosedOpenedBracketCount === 0 && $tokenIterator->currentTokenType() === Lexer::TOKEN_PHPDOC_EOL) {
                break;
            }

            // remove new line "*"
            if (Strings::contains($tokenIterator->currentTokenValue(), '*')) {
                $tokenValueWithoutAsterisk = Strings::replace($tokenIterator->currentTokenValue(), '#\*#');
                $annotationContent .= $tokenValueWithoutAsterisk;
            } else {
                $annotationContent .= $tokenIterator->currentTokenValue();
            }

            $tokenIterator->next();
        }

        return $this->cleanMultilineAnnotationContent($annotationContent);
    }

    private function cleanMultilineAnnotationContent(string $annotationContent): string
    {
        return Strings::replace($annotationContent, '#(\s+)\*(\s+)#m', '$1$3');
    }
}

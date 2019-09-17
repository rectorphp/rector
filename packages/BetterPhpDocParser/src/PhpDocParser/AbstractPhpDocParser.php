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

    protected function resolveAnnotationContent(TokenIterator $tokenIterator): string
    {
        $tokenIterator->pushSavePoint();

        $singleLineContent = $tokenIterator->joinUntil(
            Lexer::TOKEN_END,
            Lexer::TOKEN_PHPDOC_EOL,
            Lexer::TOKEN_CLOSE_PHPDOC
        );

        $tokenIterator->rollback();

        if ($singleLineContent === '' || Strings::match($singleLineContent, '#^\((.*?)\)$#m')) {
            $annotationContent = $singleLineContent;
            $tokenIterator->joinUntil(Lexer::TOKEN_END, Lexer::TOKEN_PHPDOC_EOL, Lexer::TOKEN_CLOSE_PHPDOC);
        } else { // multiline - content
            // skip all tokens for this annotation, so next annotation can work with tokens after this one
            $tokenIterator->pushSavePoint();

            $annotationContent = $tokenIterator->joinUntil(Lexer::TOKEN_END, Lexer::TOKEN_CLOSE_PHPDOC);
            if (! Strings::match($annotationContent, '#\)\s+$#m')) {
                $tokenIterator->rollback();

                /** inspired at @see \PHPStan\PhpDocParser\Parser\PhpDocParser::parseText() */
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
            } else {
                $tokenIterator->dropSavePoint();
            }
        }

        return $this->cleanMultilineAnnotationContent($annotationContent);
    }

    private function cleanMultilineAnnotationContent(string $annotationContent): string
    {
        return Strings::replace($annotationContent, '#(\s+)\*(\s+)#m', '$1$3');
    }
}

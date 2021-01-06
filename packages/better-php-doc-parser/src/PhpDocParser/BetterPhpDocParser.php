<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Rector\BetterPhpDocParser\TagResolver;
use Rector\BetterPhpDocParser\TagToPhpDocNodeFactoryMatcher;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\PhpdocParserPrinter\Mapper\NodeMapper;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class BetterPhpDocParser extends PhpDocParser
{
    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var AnnotationContentResolver
     */
    private $annotationContentResolver;

    /**
     * @var NodeMapper
     */
    private $nodeMapper;

    /**
     * @var TagToPhpDocNodeFactoryMatcher
     */
    private $tagToPhpDocNodeFactoryMatcher;

    /**
     * @var TagResolver
     */
    private $tagResolver;

    public function __construct(
        TypeParser $typeParser,
        ConstExprParser $constExprParser,
        CurrentNodeProvider $currentNodeProvider,
        ClassAnnotationMatcher $classAnnotationMatcher,
        TagToPhpDocNodeFactoryMatcher $tagToPhpDocNodeFactoryMatcher,
        AnnotationContentResolver $annotationContentResolver,
        PrivatesCaller $privatesCaller,
        PrivatesAccessor $privatesAccessor,
        TagResolver $tagResolver,
        NodeMapper $nodeMapper
    ) {
        parent::__construct($typeParser, $constExprParser);

        $this->currentNodeProvider = $currentNodeProvider;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->annotationContentResolver = $annotationContentResolver;
        $this->nodeMapper = $nodeMapper;
        $this->tagToPhpDocNodeFactoryMatcher = $tagToPhpDocNodeFactoryMatcher;
        $this->privatesCaller = $privatesCaller;
        $this->privatesAccessor = $privatesAccessor;
        $this->tagResolver = $tagResolver;
    }

    public function parseString(string $docBlock): PhpDocNode
    {
        $tokens = $this->lexer->tokenize($docBlock);
        $tokenIterator = new SmartTokenIterator($tokens);

        return $this->parse($tokenIterator);
    }
}

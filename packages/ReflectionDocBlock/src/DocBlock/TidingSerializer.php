<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use Nette\Utils\Strings;
use phpDocumentor\Reflection\DocBlock;
use Symplify\BetterReflectionDocBlock\FixedSerializer;

final class TidingSerializer
{
    /**
     * @var FixedSerializer
     */
    private $fixedSerializer;

    public function __construct(FixedSerializer $fixedSerializer)
    {
        $this->fixedSerializer = $fixedSerializer;
    }

    public function getDocComment(DocBlock $docBlock): string
    {
        $docComment = $this->fixedSerializer->getDocComment($docBlock);

        $docComment = $this->clearUnnededPreslashes($docComment);

        return $this->clearUnnededSpaces($docComment);
    }

    /**
     * phpDocumentor adds extra preslashes,
     * maybe could be resolved with custom @see DocBlock\Tags\Formatter instead of @see PassthroughFormatter
     */
    private function clearUnnededPreslashes(string $content): string
    {
        $content = str_replace('@var \\', '@var ', $content);

        return str_replace('@param \\', '@param ', $content);
    }

    /**
     * phpDocumentor adds extra spaces before Doctrine-Annotation based annotations
     * starting with uppercase and followed by (, e.g. @Route('value')
     */
    private function clearUnnededSpaces(string $content): string
    {
        return Strings::replace($content, '#@[A-Z][a-z]+\s\(#', function (array $match) {
            return str_replace(' ', '', $match[0]);
        });
    }
}

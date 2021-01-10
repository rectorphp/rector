<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use Rector\PhpdocParserPrinter\Contract\AttributeAwareInterface;
use Rector\PhpdocParserPrinter\Contract\PhpDocNodeFactoryInterface;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;
use Rector\PhpdocParserPrinter\ValueObject\Tag;
use Rector\Symfony\ValueObject\PhpDocNode\RequiredTagValueNode;

final class RequiredTagNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface
{
    /**
     * @var string
     */
    private const TAG_NAME = '@require';

    /**
     * @return RequiredTagValueNode|null
     */
    public function create(SmartTokenIterator $smartTokenIterator, Tag $annotationClass): ?AttributeAwareInterface
    {
        return new RequiredTagValueNode();
    }

    public function isMatch(Tag $tag): bool
    {
        return $tag->isMatch(self::TAG_NAME);
    }
}

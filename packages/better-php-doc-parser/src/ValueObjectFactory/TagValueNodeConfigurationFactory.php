<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioRouteTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\Utils\ArrayItemStaticHelper;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;

final class TagValueNodeConfigurationFactory
{
    public function createFromOriginalContent(
        ?string $originalContent,
        PhpDocTagValueNode $phpDocTagValueNode
    ): TagValueNodeConfiguration {
        if ($originalContent === null) {
            return new TagValueNodeConfiguration();
        }

        $silentKey = $this->resolveSilentKey($phpDocTagValueNode);
        $orderedVisibleItems = ArrayItemStaticHelper::resolveAnnotationItemsOrder($originalContent, $silentKey);

        $hasNewlineAfterOpening = (bool) Strings::match($originalContent, '#^(\(\s+|\n)#m');
        $hasNewlineBeforeClosing = (bool) Strings::match($originalContent, '#(\s+\)|\n(\s+)?)$#m');

        $hasOpeningBracket = (bool) Strings::match($originalContent, '#^\(#');
        $hasClosingBracket = (bool) Strings::match($originalContent, '#\)$#');

        $keysByQuotedStatus = [];
        foreach ($orderedVisibleItems as $orderedVisibleItem) {
            $keysByQuotedStatus[$orderedVisibleItem] = $this->isKeyQuoted(
                $originalContent,
                $orderedVisibleItem,
                $silentKey
            );
        }

        $isSilentKeyExplicit = (bool) Strings::contains($originalContent, sprintf('%s=', $silentKey));

        $arrayEqualSign = $this->resolveArrayEqualSignByPhpNodeClass($phpDocTagValueNode);

        return new TagValueNodeConfiguration(
            $originalContent,
            $orderedVisibleItems,
            $hasNewlineAfterOpening,
            $hasNewlineBeforeClosing,
            $hasOpeningBracket,
            $hasClosingBracket,
            $keysByQuotedStatus,
            $silentKey,
            $isSilentKeyExplicit,
            $arrayEqualSign
        );
    }

    private function isKeyQuoted(string $originalContent, string $key, ?string $silentKey): bool
    {
        $escapedKey = preg_quote($key, '#');

        $quotedKeyPattern = $this->createQuotedKeyPattern($silentKey, $key, $escapedKey);
        if ((bool) Strings::match($originalContent, $quotedKeyPattern)) {
            return true;
        }

        // @see https://regex101.com/r/VgvK8C/5/
        $quotedArrayPattern = sprintf('#%s=\{"(.*)"\}|\{"(.*)"\}#', $escapedKey);

        return (bool) Strings::match($originalContent, $quotedArrayPattern);
    }

    private function createQuotedKeyPattern(?string $silentKey, string $key, string $escapedKey): string
    {
        if ($silentKey === $key) {
            // @see https://regex101.com/r/VgvK8C/4/
            return sprintf('#(%s=")|\("#', $escapedKey);
        }

        // @see https://regex101.com/r/VgvK8C/3/
        return sprintf('#%s="#', $escapedKey);
    }

    /**
     * Before:
     * (options={"key":"value"})
     *
     * After:
     * (options={"key"="value"})
     *
     * @see regex https://regex101.com/r/XfKi4A/1/
     *
     * @see https://github.com/rectorphp/rector/issues/3225
     * @see https://github.com/rectorphp/rector/pull/3241
     */
    private function resolveArrayEqualSignByPhpNodeClass(PhpDocTagValueNode $phpDocTagValueNode): string
    {
        if ($phpDocTagValueNode instanceof SymfonyRouteTagValueNode) {
            return '=';
        }

        if ($phpDocTagValueNode instanceof DoctrineTagNodeInterface) {
            return '=';
        }

        if ($phpDocTagValueNode instanceof SensioRouteTagValueNode) {
            return '=';
        }

        return ':';
    }

    private function resolveSilentKey(PhpDocTagValueNode $phpDocTagValueNode): ?string
    {
        if ($phpDocTagValueNode instanceof SilentKeyNodeInterface) {
            return $phpDocTagValueNode->getSilentKey();
        }

        return null;
    }
}

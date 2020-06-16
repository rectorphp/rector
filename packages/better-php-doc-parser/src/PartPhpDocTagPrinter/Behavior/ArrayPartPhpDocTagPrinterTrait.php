<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PartPhpDocTagPrinter\Behavior;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Sensio\SensioRouteTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\TagValueNodeConfiguration;

trait ArrayPartPhpDocTagPrinterTrait
{
    /**
     * @param mixed[] $item
     */
    public function printArrayItem(
        array $item,
        ?string $key,
        TagValueNodeConfiguration $tagValueNodeConfiguration
    ): string {
        $json = Json::encode($item);

        // separate by space only items separated by comma, not in "quotes"
        $json = Strings::replace($json, '#,#', ', ');
        // @see https://regex101.com/r/C2fDQp/2
        $json = Strings::replace($json, '#("[^",]+)(\s+)?,(\s+)?([^"]+")#', '$1,$4');

        // change brackets from json to annotations
        $json = Strings::replace($json, '#^\[(.*?)\]$#', '{$1}');

        // cleanup json encoded extra slashes
        $json = Strings::replace($json, '#\\\\\\\\#', '\\');

        $json = $this->replaceColonWithEqualInSymfonyAndDoctrine($json);

        $keyPart = $this->createKeyPart($key, $tagValueNodeConfiguration);

        // should unquote
        if ($this->isValueWithoutQuotes($key, $tagValueNodeConfiguration)) {
            $json = Strings::replace($json, '#"#');
        }

        if ($tagValueNodeConfiguration->getOriginalContent() !== null && $key !== null) {
            $json = $this->quoteKeys($item, $key, $json, $tagValueNodeConfiguration->getOriginalContent());
        }

        return $keyPart . $json;
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
    private function replaceColonWithEqualInSymfonyAndDoctrine(string $json): string
    {
        if (! $this instanceof SymfonyRouteTagValueNode && ! $this instanceof DoctrineTagNodeInterface && ! $this instanceof SensioRouteTagValueNode) {
            return $json;
        }

        return Strings::replace($json, '#(\"|\w)\:(\"|\w)#', '$1=$2');
    }

    private function isValueWithoutQuotes(?string $key, TagValueNodeConfiguration $tagValueNodeConfiguration): bool
    {
        if ($key === null) {
            return false;
        }

        if (! array_key_exists($key, $tagValueNodeConfiguration->getKeysByQuotedStatus())) {
            return false;
        }

        return ! $tagValueNodeConfiguration->getKeysByQuotedStatus()[$key];
    }

    private function createKeyPart(?string $key, TagValueNodeConfiguration $tagValueNodeConfiguration): string
    {
        if (empty($key)) {
            return '';
        }

        if ($key === $tagValueNodeConfiguration->getSilentKey() && ! $tagValueNodeConfiguration->isSilentKeyExplicit()) {
            return '';
        }

        return $key . '=';
    }

    private function quoteKeys(array $item, string $key, string $json, string $originalContent): string
    {
        foreach (array_keys($item) as $itemKey) {
            // @see https://regex101.com/r/V7nq5D/1
            $quotedKeyPattern = '#' . $key . '={(.*?)?\"' . $itemKey . '\"(=|:)(.*?)?}#';
            $isKeyQuoted = (bool) Strings::match($originalContent, $quotedKeyPattern);
            if (! $isKeyQuoted) {
                continue;
            }

            $json = Strings::replace($json, '#([^\"])' . $itemKey . '([^\"])#', '$1"' . $itemKey . '"$2');
        }

        return $json;
    }
}

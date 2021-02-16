<?php

namespace Rector\CodeQuality\Tests\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\Fixture;

final class DemoFile
{
    private string $cachedBaseRoutePattern;
    
    public function getBaseRoutePattern(): string
    {
        if (isset($this->cachedBaseRoutePattern)) {
            return $this->cachedBaseRoutePattern;
        }

        $this->cachedBaseRoutePattern = sprintf(
            '/%s%s',
            $matches[1] === '' ? '' : $this->urlize($matches[1], '-') . '/',
            $this->urlize($matches[3], '-')
        );

        return $this->cachedBaseRoutePattern;
    }
}
?>

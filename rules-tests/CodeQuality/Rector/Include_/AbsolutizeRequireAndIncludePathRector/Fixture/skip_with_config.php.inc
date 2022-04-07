<?php

namespace Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector\Source\Module;

describe('Module', function (): void {

    beforeAll(function (): void {
        $this->module = new Module();
    });

    describe('->getConfig()', function (): void {

        it('returns config', function (): void {
            $expected = include 'config/module.config.php';
            expect($this->module->getConfig())->toBe($expected);
        });

    });

});

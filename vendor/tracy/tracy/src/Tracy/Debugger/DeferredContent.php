<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Tracy;

/**
 * @internal
 */
final class DeferredContent
{
    /**
     * @var \Tracy\SessionStorage
     */
    private $sessionStorage;
    /**
     * @var string
     */
    private $requestId;
    /**
     * @var bool
     */
    private $useSession = \false;
    public function __construct(SessionStorage $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
        $this->requestId = $_SERVER['HTTP_X_TRACY_AJAX'] ?? Helpers::createId();
    }
    public function isAvailable() : bool
    {
        return $this->useSession && $this->sessionStorage->isAvailable();
    }
    public function getRequestId() : string
    {
        return $this->requestId;
    }
    public function &getItems(string $key) : array
    {
        $items =& $this->sessionStorage->getData()[$key];
        $items = (array) $items;
        return $items;
    }
    /**
     * @param mixed $argument
     */
    public function addSetup(string $method, $argument) : void
    {
        $argument = \json_encode($argument, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE);
        $item =& $this->getItems('setup')[$this->requestId];
        $item['code'] = ($item['code'] ?? '') . "{$method}({$argument});\n";
        $item['time'] = \time();
    }
    public function sendAssets() : bool
    {
        if (\headers_sent($file, $line) || \ob_get_length()) {
            throw new \LogicException(__METHOD__ . '() called after some output has been sent. ' . ($file ? "Output started at {$file}:{$line}." : 'Try Tracy\\OutputDebugger to find where output started.'));
        }
        $asset = $_GET['_tracy_bar'] ?? null;
        if ($asset === 'js') {
            \header('Content-Type: application/javascript; charset=UTF-8');
            \header('Cache-Control: max-age=864000');
            \header_remove('Pragma');
            \header_remove('Set-Cookie');
            $str = $this->buildJsCss();
            \header('Content-Length: ' . \strlen($str));
            echo $str;
            \flush();
            return \true;
        }
        $this->useSession = $this->sessionStorage->isAvailable();
        if (!$this->useSession) {
            return \false;
        }
        $this->clean();
        if (\is_string($asset) && \preg_match('#^content(-ajax)?\\.(\\w+)$#', $asset, $m)) {
            [, $ajax, $requestId] = $m;
            \header('Content-Type: application/javascript; charset=UTF-8');
            \header('Cache-Control: max-age=60');
            \header_remove('Set-Cookie');
            $str = $ajax ? '' : $this->buildJsCss();
            $data =& $this->getItems('setup');
            $str .= $data[$requestId]['code'] ?? '';
            unset($data[$requestId]);
            \header('Content-Length: ' . \strlen($str));
            echo $str;
            \flush();
            return \true;
        }
        if (Helpers::isAjax()) {
            \header('X-Tracy-Ajax: 1');
            // session must be already locked
        }
        return \false;
    }
    private function buildJsCss() : string
    {
        $css = \array_map('file_get_contents', \array_merge([__DIR__ . '/../assets/reset.css', __DIR__ . '/../Bar/assets/bar.css', __DIR__ . '/../assets/toggle.css', __DIR__ . '/../assets/table-sort.css', __DIR__ . '/../assets/tabs.css', __DIR__ . '/../Dumper/assets/dumper-light.css', __DIR__ . '/../Dumper/assets/dumper-dark.css', __DIR__ . '/../BlueScreen/assets/bluescreen.css'], Debugger::$customCssFiles));
        $js1 = \array_map(function ($file) {
            return '(function() {' . \file_get_contents($file) . '})();';
        }, [__DIR__ . '/../Bar/assets/bar.js', __DIR__ . '/../assets/toggle.js', __DIR__ . '/../assets/table-sort.js', __DIR__ . '/../assets/tabs.js', __DIR__ . '/../Dumper/assets/dumper.js', __DIR__ . '/../BlueScreen/assets/bluescreen.js']);
        $js2 = \array_map('file_get_contents', Debugger::$customJsFiles);
        $str = "'use strict';\n(function(){\n\tvar el = document.createElement('style');\n\tel.setAttribute('nonce', document.currentScript.getAttribute('nonce') || document.currentScript.nonce);\n\tel.className='tracy-debug';\n\tel.textContent=" . \json_encode(Helpers::minifyCss(\implode('', $css))) . ";\n\tdocument.head.appendChild(el);})\n();\n" . \implode('', $js1) . \implode('', $js2);
        return $str;
    }
    public function clean() : void
    {
        foreach ($this->sessionStorage->getData() as &$items) {
            $items = \array_slice((array) $items, -10, null, \true);
            $items = \array_filter($items, function ($item) {
                return isset($item['time']) && $item['time'] > \time() - 60;
            });
        }
    }
}

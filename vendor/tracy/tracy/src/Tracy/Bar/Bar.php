<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220418\Tracy;

/**
 * Debug Bar.
 */
class Bar
{
    /** @var IBarPanel[] */
    private $panels = [];
    /** @var bool */
    private $loaderRendered = \false;
    /**
     * Add custom panel.
     * @return static
     */
    public function addPanel(\RectorPrefix20220418\Tracy\IBarPanel $panel, ?string $id = null) : self
    {
        if ($id === null) {
            $c = 0;
            do {
                $id = \get_class($panel) . ($c++ ? "-{$c}" : '');
            } while (isset($this->panels[$id]));
        }
        $this->panels[$id] = $panel;
        return $this;
    }
    /**
     * Returns panel with given id
     */
    public function getPanel(string $id) : ?\RectorPrefix20220418\Tracy\IBarPanel
    {
        return $this->panels[$id] ?? null;
    }
    /**
     * Renders loading <script>
     * @internal
     */
    public function renderLoader(\RectorPrefix20220418\Tracy\DeferredContent $defer) : void
    {
        if (!$defer->isAvailable()) {
            throw new \LogicException('Start session before Tracy is enabled.');
        }
        $this->loaderRendered = \true;
        $requestId = $defer->getRequestId();
        $nonce = \RectorPrefix20220418\Tracy\Helpers::getNonce();
        $async = \true;
        require __DIR__ . '/assets/loader.phtml';
    }
    /**
     * Renders debug bar.
     */
    public function render(\RectorPrefix20220418\Tracy\DeferredContent $defer) : void
    {
        $redirectQueue =& $defer->getItems('redirect');
        $requestId = $defer->getRequestId();
        if (\RectorPrefix20220418\Tracy\Helpers::isAjax()) {
            if ($defer->isAvailable()) {
                $defer->addSetup('Tracy.Debug.loadAjax', $this->renderPartial('ajax', '-ajax:' . $requestId));
            }
        } elseif (\RectorPrefix20220418\Tracy\Helpers::isRedirect()) {
            if ($defer->isAvailable()) {
                $redirectQueue[] = ['content' => $this->renderPartial('redirect', '-r' . \count($redirectQueue)), 'time' => \time()];
            }
        } elseif (\RectorPrefix20220418\Tracy\Helpers::isHtmlMode()) {
            $content = $this->renderPartial('main');
            foreach (\array_reverse($redirectQueue) as $item) {
                $content['bar'] .= $item['content']['bar'];
                $content['panels'] .= $item['content']['panels'];
            }
            $redirectQueue = null;
            $content = '<div id=tracy-debug-bar>' . $content['bar'] . '</div>' . $content['panels'];
            if ($this->loaderRendered) {
                $defer->addSetup('Tracy.Debug.init', $content);
            } else {
                $nonce = \RectorPrefix20220418\Tracy\Helpers::getNonce();
                $async = \false;
                \RectorPrefix20220418\Tracy\Debugger::removeOutputBuffers(\false);
                require __DIR__ . '/assets/loader.phtml';
            }
        }
    }
    private function renderPartial(string $type, string $suffix = '') : array
    {
        $panels = $this->renderPanels($suffix);
        return ['bar' => \RectorPrefix20220418\Tracy\Helpers::capture(function () use($type, $panels) {
            require __DIR__ . '/assets/bar.phtml';
        }), 'panels' => \RectorPrefix20220418\Tracy\Helpers::capture(function () use($type, $panels) {
            require __DIR__ . '/assets/panels.phtml';
        })];
    }
    private function renderPanels(string $suffix = '') : array
    {
        \set_error_handler(function (int $severity, string $message, string $file, int $line) {
            if (\error_reporting() & $severity) {
                throw new \ErrorException($message, 0, $severity, $file, $line);
            }
        });
        $obLevel = \ob_get_level();
        $panels = [];
        foreach ($this->panels as $id => $panel) {
            $idHtml = \preg_replace('#[^a-z0-9]+#i', '-', $id) . $suffix;
            try {
                $tab = (string) $panel->getTab();
                $panelHtml = $tab ? $panel->getPanel() : null;
            } catch (\Throwable $e) {
                while (\ob_get_level() > $obLevel) {
                    // restore ob-level if broken
                    \ob_end_clean();
                }
                $idHtml = "error-{$idHtml}";
                $tab = "Error in {$id}";
                $panelHtml = "<h1>Error: {$id}</h1><div class='tracy-inner'>" . \nl2br(\RectorPrefix20220418\Tracy\Helpers::escapeHtml($e)) . '</div>';
                unset($e);
            }
            $panels[] = (object) ['id' => $idHtml, 'tab' => $tab, 'panel' => $panelHtml];
        }
        \restore_error_handler();
        return $panels;
    }
}

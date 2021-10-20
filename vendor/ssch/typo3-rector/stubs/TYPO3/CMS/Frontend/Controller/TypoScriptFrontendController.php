<?php

namespace RectorPrefix20211020\TYPO3\CMS\Frontend\Controller;

use RectorPrefix20211020\Psr\Http\Message\ResponseInterface;
use RectorPrefix20211020\TYPO3\CMS\Core\Charset\CharsetConverter;
use RectorPrefix20211020\TYPO3\CMS\Core\Context\Context;
use RectorPrefix20211020\TYPO3\CMS\Core\Page\PageRenderer;
use RectorPrefix20211020\TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use RectorPrefix20211020\TYPO3\CMS\Core\TypoScript\TemplateService;
use RectorPrefix20211020\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use RectorPrefix20211020\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use RectorPrefix20211020\TYPO3\CMS\Frontend\Page\PageRepository;
if (\class_exists('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController')) {
    return;
}
class TypoScriptFrontendController
{
    /**
     * @var array
     */
    public $cHash_array = [];
    /**
     * @var string
     */
    public $cHash = '';
    /**
     * @var int
     */
    public $domainStartPage = 0;
    /**
     * @var string
     */
    public $xhtmlDoctype = '';
    /**
     * @var ContentObjectRenderer
     */
    public $cObj = '';
    /**
     * @var string
     */
    public $loginUser = '';
    /**
     * @var string
     */
    public $gr_list = '';
    /**
     * @var string
     */
    public $beUserLogin = '';
    /**
     * @var string
     */
    public $showHiddenPage = '';
    /**
     * @var string
     */
    public $showHiddenRecords = '';
    /**
     * @var bool
     */
    public $forceTemplateParsing = \false;
    /**
     * @var TemplateService
     */
    public $tmpl;
    /**
     * @var PageRepository
     */
    public $sys_page;
    /**
     * @var SiteLanguage
     */
    protected $language;
    /**
     * @var string
     */
    public $sys_language_isocode;
    /**
     * @var CharsetConverter
     */
    public $csConvObj;
    /**
     * @var int
     */
    public $sys_language_uid = 0;
    /**
     * @var string
     */
    public $sys_language_mode = '';
    /**
     * @var int
     */
    public $sys_language_content = 0;
    /**
     * @var int
     */
    public $sys_language_contentOL = 0;
    /**
     * @var int
     */
    public $ADMCMD_preview_BEUSER_uid = 0;
    /**
     * @var int
     */
    public $workspacePreview = 0;
    /**
     * @var bool
     */
    public $loginAllowedInBranch = \false;
    /**
     * @var FrontendUserAuthentication
     */
    public $fe_user;
    /**
     * @var string
     */
    public $renderCharset = '';
    /**
     * @return void
     */
    public function initTemplate()
    {
    }
    /**
     * @return bool
     */
    public function checkIfLoginAllowedInBranch()
    {
        return \false;
    }
    public function __construct()
    {
        //fake template object, otherwise tests cannot access this property
        $this->tmpl = new \RectorPrefix20211020\TYPO3\CMS\Core\TypoScript\TemplateService();
        $this->sys_page = new \RectorPrefix20211020\TYPO3\CMS\Frontend\Page\PageRepository();
        $this->language = new \RectorPrefix20211020\TYPO3\CMS\Core\Site\Entity\SiteLanguage();
        $this->sys_language_isocode = 'ch';
        $this->csConvObj = new \RectorPrefix20211020\TYPO3\CMS\Core\Charset\CharsetConverter();
        $this->cObj = new \RectorPrefix20211020\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
        $this->fe_user = new \RectorPrefix20211020\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication();
    }
    /**
     * @return void
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function applyHttpHeadersToResponse($response)
    {
    }
    /**
     * @return void
     */
    public function processContentForOutput()
    {
    }
    /**
     * @return void
     */
    public function processOutput()
    {
    }
    /**
     * @return void
     */
    public function settingLocale()
    {
    }
    /**
     * @return \TYPO3\CMS\Core\Site\Entity\SiteLanguage
     */
    public function getLanguage()
    {
        return $this->language;
    }
    /**
     * @return void
     */
    public function settingLanguage()
    {
    }
    /**
     * @return \TYPO3\CMS\Core\Page\PageRenderer
     */
    public function getPageRenderer()
    {
        return new \RectorPrefix20211020\TYPO3\CMS\Core\Page\PageRenderer();
    }
    /**
     * @return mixed[]
     */
    public function getPageShortcut($SC, $mode, $thisUid, $itera = 20, $pageLog = [], $disableGroupCheck = \false)
    {
        return [];
    }
    /**
     * @return void
     */
    public function csConv($str, $from = '')
    {
    }
    /**
     * @return void
     */
    public function pageUnavailableAndExit($reason = '', $header = '')
    {
    }
    /**
     * @return void
     */
    public function pageNotFoundAndExit($reason = '', $header = '')
    {
    }
    /**
     * @return void
     */
    public function checkPageUnavailableHandler()
    {
    }
    /**
     * @return void
     */
    public function pageUnavailableHandler($code, $header, $reason)
    {
    }
    /**
     * @return void
     */
    public function pageNotFoundHandler($code, $header = '', $reason = '')
    {
    }
    /**
     * @return void
     */
    public function pageErrorHandler($code, $header = '', $reason = '')
    {
    }
    /**
     * @return void
     */
    public function setContentType($contentType)
    {
    }
    /**
     * @return void
     */
    public function initFEuser()
    {
    }
    /**
     * @return void
     */
    public function storeSessionData()
    {
    }
    /**
     * @return void
     */
    public function previewInfo()
    {
    }
    /**
     * @return void
     */
    public function hook_eofe()
    {
    }
    /**
     * @return void
     */
    public function addTempContentHttpHeaders()
    {
    }
    /**
     * @return void
     */
    public function sendCacheHeaders()
    {
    }
}

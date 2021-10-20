<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Authentication;

if (\class_exists('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication')) {
    return;
}
class BackendUserAuthentication
{
    /**
     * @var array
     */
    public $userTS = ['tx_news.' => ['singleCategoryAcl' => 1]];
    /**
     * @return mixed[]
     */
    public function getTSConfig($objectString = null, $config = null)
    {
        return [];
    }
    /**
     * @return int
     */
    public function simplelog($message, $extKey = '', $error = 0)
    {
        return 1;
    }
    public function getTSConfigVal($objectString)
    {
        $TSConf = $this->getTSConfig($objectString);
        return $TSConf['value'];
    }
    public function getTSConfigProp($objectString)
    {
        $TSConf = $this->getTSConfig($objectString);
        return $TSConf['properties'];
    }
}

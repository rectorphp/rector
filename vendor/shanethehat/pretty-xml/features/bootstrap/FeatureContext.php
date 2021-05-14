<?php

namespace RectorPrefix20210514;

use RectorPrefix20210514\Behat\Behat\Tester\Exception\PendingException;
use RectorPrefix20210514\Behat\Behat\Context\SnippetAcceptingContext;
use RectorPrefix20210514\PrettyXml\Formatter;
/**
 * Behat context class.
 */
class FeatureContext implements \RectorPrefix20210514\Behat\Behat\Context\SnippetAcceptingContext
{
    /**
     * @var string
     */
    private $fixtureType;
    /**
     * @var string
     */
    private $formattedXml;
    /**
     * @Given I have a :type xml file
     */
    public function iHaveAXmlFile($type)
    {
        $this->fixtureType = \str_replace(' ', '_', \strtolower($type));
    }
    /**
     * @When it is formatted by PrettyXML
     */
    public function itIsFormattedByPrettyXml()
    {
        $formatter = new \RectorPrefix20210514\PrettyXml\Formatter();
        $this->formattedXml = $formatter->format($this->getBeforeXml());
    }
    /**
     * @Then it should be correctly formatted
     */
    public function itShouldBeCorrectlyFormatted()
    {
        \RectorPrefix20210514\expect($this->formattedXml)->toBe($this->getAfterXml());
    }
    /**
     * @return string
     */
    private function getBeforeXml()
    {
        return \file_get_contents(\sprintf('%s/fixtures/before/%s.xml', __DIR__, $this->fixtureType));
    }
    /**
     * @return string
     */
    private function getAfterXml()
    {
        return \file_get_contents(\sprintf('%s/fixtures/after/%s.xml', __DIR__, $this->fixtureType));
    }
}
/**
 * Behat context class.
 */
\class_alias('RectorPrefix20210514\\FeatureContext', 'FeatureContext', \false);

<?php

namespace RectorPrefix20211020\TYPO3\CMS\Core\Mail;

if (\class_exists('TYPO3\\CMS\\Core\\Mail\\MailMessage')) {
    return;
}
class MailMessage
{
    /**
     * @return $this
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $subject = (string) $subject;
        return $this;
    }
    /**
     * @return $this
     */
    public function setFrom($addresses, $name = null)
    {
        return $this;
    }
    /**
     * @return $this
     */
    public function setTo($addresses, $name = null)
    {
        return $this;
    }
    /**
     * @return $this
     */
    public function setBody($body, $contentType = null, $charset = null)
    {
        return $this;
    }
    /**
     * @param string $charset
     */
    public function text($body, $charset = 'utf-8')
    {
        $charset = (string) $charset;
        return $this;
    }
    /**
     * @param string $charset
     */
    public function html($body, $charset = 'utf-8')
    {
        $charset = (string) $charset;
        return $this;
    }
    /**
     * @return $this
     * @param string $body
     */
    public function addPart($body, $contentType = null, $charset = null)
    {
        $body = (string) $body;
        return $this;
    }
    /**
     * @return $this
     */
    public function attach($attachment)
    {
        return $this;
    }
    /**
     * @return $this
     */
    public function embed($embed)
    {
        return $this;
    }
    /**
     * @return $this
     */
    public function embedFromPath($embed)
    {
        return $this;
    }
    /**
     * @return $this
     * @param string $path
     * @param string $name
     * @param string $contentType
     */
    public function attachFromPath($path, $name = null, $contentType = null)
    {
        $path = (string) $path;
        $name = (string) $name;
        $contentType = (string) $contentType;
        return $this;
    }
    /**
     * @return void
     */
    public function send()
    {
    }
}

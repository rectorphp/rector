<?php

namespace RectorPrefix20211020\ApacheSolrForTypo3\Solr\System\Solr\Document;

if (\class_exists('ApacheSolrForTypo3\\Solr\\System\\Solr\\Document\\Document')) {
    return;
}
class Document
{
    public function __set($name, $value)
    {
    }
    public function __get($name)
    {
    }
    public function __unset($name)
    {
    }
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Solr\Document\Document
     */
    public function addField($key, $value, $boost = null, $modifier = null)
    {
        return $this;
    }
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Solr\Document\Document
     */
    public function setField($key, $value, $boost = null, $modifier = null)
    {
        return $this;
    }
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Solr\Document\Document
     */
    public function removeField($key)
    {
        return $this;
    }
    /**
     * @return float|null
     */
    public function getFieldBoost($key)
    {
        return null;
    }
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Solr\Document\Document
     */
    public function setFieldBoost($key, $boost)
    {
        return $this;
    }
    /**
     * @return mixed[]
     */
    public function getFieldBoosts()
    {
        return [];
    }
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Solr\Document\Document
     */
    public function setBoost($boost)
    {
        return $this;
    }
    /**
     * @return float|null
     */
    public function getBoost()
    {
        return null;
    }
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Solr\Document\Document
     */
    public function clear()
    {
        return $this;
    }
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Solr\Document\Document
     */
    public function setKey($key, $value = null)
    {
        return $this;
    }
    /**
     * @return \ApacheSolrForTypo3\Solr\System\Solr\Document\Document
     */
    public function setFieldModifier($key, $modifier = null)
    {
        return $this;
    }
    /**
     * @return mixed[]
     */
    public function getFields()
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle\Model;

use Elastica\ResultSet;

class SearchResult implements \Iterator, \Countable, \JsonSerializable
{
    /**
     * results
     *
     * @var \Iterator
     */
    private $results;

    /**
     * position
     *
     * @var int
     */
    private $position;

    /**
     * nextPage
     *
     * @var int
     */
    private $nextPage;

    /**
     * baseResults
     *
     * @var ResultSet
     */
    private $baseResults;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->results = [];
        $this->position = 0;
    }

    /**
     * __call
     *
     * @param string $name method name
     * @param array $arguments arguments
     *
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        return call_user_func_array([$this->baseResults, $name], $arguments);
    }

    /**
     * setResults
     *
     * @param \Iterator $results
     *
     * @return SearchResult
     */
    public function setResults(\ArrayAccess $results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * Gets the value of baseResults
     *
     * @return ResultSet
     */
    public function getBaseResults()
    {
        return $this->baseResults;
    }

    /**
     * Sets the value of baseResults
     *
     * @param ResultSet $baseResults description
     *
     * @return SearchResult
     */
    public function setBaseResults(ResultSet $baseResults)
    {
        $this->baseResults = $baseResults;

        return $this;
    }

    /**
     * current
     *
     * @return mixed
     */
    public function current()
    {
        return $this->results[$this->position];
    }

    /**
     * key
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * next
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * rewind
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * valid
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->results[$this->position]);
    }

    /**
     * count
     *
     * @return int
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * setNextPage
     *
     * @param int $page
     *
     * @return SearchResult
     */
    public function setNextPage($page)
    {
        $this->nextPage = $page;

        return $this;
    }

    /**
     * getNextPage
     *
     * @return int
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    public function jsonSerialize()
    {
        return $this->results;
    }
}

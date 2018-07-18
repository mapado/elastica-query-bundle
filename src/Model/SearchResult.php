<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle\Model;

use Elastica\ResultSet;

class SearchResult implements \Iterator, \Countable, \JsonSerializable
{
    /**
     * results
     *
     * @var array|\ArrayAccess
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
     * @var ?int
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
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $callable = [$this->baseResults, $name];
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf('method %s::%s is not callable', get_class($this->baseResults), $name));
        }

        return call_user_func_array($callable, $arguments);
    }

    /**
     * setResults
     */
    public function setResults(\ArrayAccess $results): self
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
        if (is_array($this->results) || $this->results instanceof \Countable) {
            return count($this->results);
        }

        return count($this);
    }

    /**
     * setNextPage
     */
    public function setNextPage(?int $page): self
    {
        $this->nextPage = $page;

        return $this;
    }

    /**
     * getNextPage
     */
    public function getNextPage(): ?int
    {
        return $this->nextPage;
    }

    public function jsonSerialize()
    {
        return $this->results;
    }
}

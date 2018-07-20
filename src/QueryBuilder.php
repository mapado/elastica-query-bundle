<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Filter;
use Elastica\Filter\AbstractFilter;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\BoolFilter;
use Elastica\Query\AbstractQuery;
use Elastica\Query as ElasticaQuery;
use Mapado\ElasticaQueryBundle\Model\SearchResult;

class QueryBuilder
{
    /**
     * documentManager
     *
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * filterList
     *
     * @var array
     */
    private $filterList;

    /**
     * queryList
     *
     * @var array
     */
    private $queryList;

    /**
     * sortList
     *
     * @var array
     */
    private $sortList;

    /**
     * aggregationList
     *
     * @var array
     */
    private $aggregationList;

    /**
     * firstResults
     *
     * @var int
     */
    private $firstResults;

    /**
     * maxResults
     *
     * @var int
     */
    private $maxResults;

    /**
     * minScore
     *
     * @var int
     */
    private $minScore;

    /**
     * __construct
     *
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
        $this->filterList = [];
        $this->queryList = [];
        $this->sortList = [];
    }

    /**
     * addFilter
     */
    public function addFilter(AbstractFilter $filter): self
    {
        $this->filterList[] = $filter;

        return $this;
    }

    /**
     * addQuery
     */
    public function addQuery(AbstractQuery $query): self
    {
        $this->queryList[] = $query;

        return $this;
    }

    /**
     * addSort
     *
     * @param mixed $sort Sort parameter
     */
    public function addSort($sort): self
    {
        $this->sortList[] = $sort;

        return $this;
    }

    /**
     * addAggregation
     */
    public function addAggregation(AbstractAggregation $aggregation): self
    {
        $this->aggregationList[] = $aggregation;

        return $this;
    }

    /**
     * setMaxResults
     */
    public function setMaxResults(int $maxResults): self
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * setFirstResults
     */
    public function setFirstResults(int $firstResults): self
    {
        $this->firstResults = $firstResults;

        return $this;
    }

    /**
     * setMinScore
     */
    public function setMinScore(int $minScore): self
    {
        $this->minScore = $minScore;

        return $this;
    }

    /**
     * getElasticQuery
     */
    public function getElasticQuery(): Query
    {
        if ($this->filterList) {
            $filteredQuery = new ElasticaQuery\Filtered($this->getQuery(), $this->getFilter());
            $query = new Query($filteredQuery);
        } else {
            $query = new Query($this->getQuery());
        }
        $query->setDocumentManager($this->documentManager);

        // manage size / from
        if ($this->firstResults) {
            $query->setFrom($this->firstResults);
        }
        if (isset($this->maxResults)) {
            $query->setSize($this->maxResults);
        }

        if (!empty($this->sortList)) {
            $query->setSort($this->sortList);
        }

        if (isset($this->minScore)) {
            $query->setMinScore($this->minScore);
        }

        if (!empty($this->aggregationList)) {
            foreach ($this->aggregationList as $aggregation) {
                $query->addAggregation($aggregation);
            }
        }

        return $query;
    }

    /**
     * getResult
     */
    public function getResult(): SearchResult
    {
        return $this->getElasticQuery()->getResult();
    }

    /**
     * getQuery
     */
    private function getQuery(): ?AbstractQuery
    {
        if (!$this->queryList) {
            return null;
        }

        if (1 == count($this->queryList)) {
            return current($this->queryList);
        }

        $query = new ElasticaQuery\BoolQuery();
        foreach ($this->queryList as $tmpQuery) {
            $query->addMust($tmpQuery);
        }

        return $query;
    }

    /**
     * getFilter
     */
    private function getFilter(): ?AbstractFilter
    {
        if (!$this->filterList) {
            return null;
        }

        if (1 == count($this->filterList)) {
            return current($this->filterList);
        }

        $boolFilters = [];
        $andFilters = [];
        foreach ($this->filterList as $tmpFilter) {
            if ($this->isAndFilter($tmpFilter)) {
                $andFilters[] = $tmpFilter;
            } else {
                $boolFilters[] = $tmpFilter;
            }
        }

        $boolFilter = null;
        $nbBoolFilters = count($boolFilters);
        if ($nbBoolFilters > 1) {
            $boolFilter = new BoolFilter();
            foreach ($boolFilters as $tmpFilter) {
                $boolFilter->addMust($tmpFilter);
            }

            array_unshift($andFilters, $boolFilter);
        } elseif (1 == $nbBoolFilters) {
            $andFilters = array_merge($boolFilters, $andFilters);
        }

        $nbAndFilters = count($andFilters);
        if (1 == $nbAndFilters) {
            return current($andFilters);
        } elseif ($nbAndFilters > 1) {
            $filter = new BoolAnd();
            $filter->setFilters($andFilters);

            return $filter;
        }

        return null;
    }

    /**
     * select if the filter is more in a `BoolAnd` or a `BoolFilter`.
     *
     * @see http://www.elasticsearch.org/blog/all-about-elasticsearch-filter-bitsets/
     */
    private function isAndFilter(AbstractFilter $filter): bool
    {
        return $filter instanceof Filter\Script
            || $filter instanceof Filter\NumericRange
            || $filter instanceof Filter\NumericRange
            || $filter instanceof Filter\GeoBoundingBox
            || $filter instanceof Filter\GeoDistance
            || $filter instanceof Filter\GeoDistanceRange
            || $filter instanceof Filter\GeoPolygon
            || $filter instanceof Filter\GeoShapePreIndexed
            || $filter instanceof Filter\GeoShapeProvided
            || $filter instanceof Filter\GeohashCell
        ;
    }
}

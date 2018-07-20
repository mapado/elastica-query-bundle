<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle;

use Elastica\Query as ElasticaQuery;
use Mapado\ElasticaQueryBundle\Model\SearchResult;

/**
 * Class Query
 *
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class Query extends ElasticaQuery
{
    /**
     * documentManager
     *
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * setDocumentManager
     */
    public function setDocumentManager(DocumentManager $documentManager): self
    {
        $this->documentManager = $documentManager;

        return $this;
    }

    /**
     * getResults
     */
    public function getResult(): SearchResult
    {
        $resultSet = $this->documentManager->getElasticType()->search($this);

        return $this->documentManager->handleResultSet($resultSet);
    }
}

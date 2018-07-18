<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle;

use Elastica\Query as ElasticaQuery;

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
     *
     * @param DocumentManager $documentManager
     *
     * @return Query
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;

        return $this;
    }

    /**
     * getResults
     */
    public function getResult()
    {
        $resultSet = $this->documentManager->getElasticType()->search($this);

        return $this->documentManager->handleResultSet($resultSet);
    }
}

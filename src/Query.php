<?php

namespace Mapado\ElasticaQueryBundle;

use Elastica\Query as ElasticaQuery;

/**
 * Class Query
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class Query extends ElasticaQuery
{
    /**
     * documentManager
     *
     * @var DocumentManager
     * @access private
     */
    private $documentManager;

    /**
     * setDocumentManager
     *
     * @param DocumentManager $documentManager
     * @access public
     * @return Query
     */
    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
        return $this;
    }

    /**
     * getResults
     *
     * @access public
     * @return void
     */
    public function getResult()
    {
        $resultSet = $this->documentManager->getElasticType()->search($this);

        return $this->documentManager->handleResultSet($resultSet);
    }
}

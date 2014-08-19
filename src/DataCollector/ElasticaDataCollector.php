<?php

namespace Mapado\ElasticaQueryBundle\DataCollector;

//use FOS\ElasticaBundle\Logger\ElasticaLogger;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ElasticaDataCollector extends DataCollector
{
    /**
     * __construct
     *
     * @access public
     */
    public function __construct()
    {
        $this->data['queries'] = [];
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    public function getQueryCount()
    {
        return count($this->data['queries']);
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }

    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $query) {
            if ($query['response']->getStatus() == 200) {
                $time += $query['response']->getData()['took'];
            }
        }

        return $time;
    }

    /**
     * addQuery
     *
     * @param array $request
     * @param \Elastica\Response $response
     * @access public
     * @return ElasticaDataCollector
     */
    public function addQuery(array $request, \Elastica\Response $response)
    {
        $this->data['queries'][] = ['request' => $request, 'response' => $response];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mapado_elastica';
    }
}

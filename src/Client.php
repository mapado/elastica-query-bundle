<?php

declare(strict_types=1);

namespace Mapado\ElasticaQueryBundle;

use Elastica\Client as BaseClient;
use Elastica\Exception\ResponseException;
use Elastica\Request;
use Mapado\ElasticaQueryBundle\DataCollector\ElasticaDataCollector;
use Symfony\Component\Stopwatch\Stopwatch;

class Client extends BaseClient
{
    /**
     * stopwatch
     *
     * @var Stopwatch|null
     */
    private $stopwatch;

    /**
     * dataCollector
     *
     * @var ElasticaDataCollector
     */
    private $dataCollector;

    /**
     * setStopwatch
     *
     * @param Stopwatch $stopwatch
     *
     * @return Client
     */
    public function setStopwatch(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;

        return $this;
    }

    /**
     * setDataCollector
     *
     * @param ElasticaDataCollector $dataCollector
     *
     * @return Client
     */
    public function setDataCollector(ElasticaDataCollector $dataCollector)
    {
        $this->dataCollector = $dataCollector;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function request($path, $method = Request::GET, $data = [], array $query = [])
    {
        $exceptionOccured = false;

        if (isset($this->stopwatch)) {
            $this->stopwatch->start('mpd_elastica', 'mapado_elastica_query');
        }

        try {
            $response = parent::request($path, $method, $data, $query);
        } catch (ResponseException $e) {
            $response = $e->getResponse();
            $exceptionOccured = true;
        }

        if (isset($this->dataCollector)) {
            $this->dataCollector->addQuery(
                ['path' => $path, 'method' => $method, 'data' => $data, 'query' => $query],
                $response
            );
        }

        if (isset($this->stopwatch)) {
            $this->stopwatch->stop('mpd_elastica');
        }

        if ($exceptionOccured) {
            throw $e;
        }

        return $response;
    }
}

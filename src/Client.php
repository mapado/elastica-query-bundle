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
     * @var ?Stopwatch
     */
    private $stopwatch;

    /**
     * dataCollector
     *
     * @var ?ElasticaDataCollector
     */
    private $dataCollector;

    /**
     * setStopwatch
     */
    public function setStopwatch(Stopwatch $stopwatch): self
    {
        $this->stopwatch = $stopwatch;

        return $this;
    }

    /**
     * setDataCollector
     */
    public function setDataCollector(ElasticaDataCollector $dataCollector): self
    {
        $this->dataCollector = $dataCollector;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function request($path, $method = Request::GET, $data = [], array $query = [])
    {
        $occuredException = null;

        if (isset($this->stopwatch)) {
            $this->stopwatch->start('mpd_elastica', 'mapado_elastica_query');
        }

        try {
            $response = parent::request($path, $method, $data, $query);
        } catch (ResponseException $e) {
            $response = $e->getResponse();
            $occuredException = $e;
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

        if (null !== $occuredException) {
            throw $occuredException;
        }

        return $response;
    }
}

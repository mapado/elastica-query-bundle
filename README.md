Elastica Query Bundle
============================

Inspired by FOSElasticaBundle and Doctrine to create a query builder for ElasticSearch.


## Installation
```sh
composer require "mapado/elastica-query-bundle:1.*"
```

## Configuation

```yaml
# app/config/config.yml
mapado_elastica_query:
    clients:
        client_name:
            host: elasticsearch.example.com
            port: 9200
            timeout: 3

    indexes:
        twitter:
            # (optional, default to 'twitter') index_name: twitter_v1
            client: client_name
            types:
                tweet: ~

    document_managers:
        tweety:
            type: mapado.elastica.type.twitter.tweet
            # data_transformer: my.model.transformer #optional, must implements Mapado\ElasticaQueryBundle\DataTransformer\DataTransformerInterface
```

## Usage
### Get elastica objects
You can fetch basic Elastica objects just by doing this:
```php
$client = $this->get('mapado.elastica.client.client_name'); // return a \Elastica\Client object
$index = $this->get('mapado.elastica.index.twitter');       // return a \Elastica\Index object
$type = $this->get('mapado.elastica.type.twitter.tweet');   // return a \Elastica\Type object
```

### Get DocumentManager
```php
$documentManager = $this->get('mapado.elastica.document_manager.tweety'); // return a \Mapado\ElasticaQueryBundle\DocumentManager
$queryBuilder = $documentManager->createQueryBuilder();

$queryBuilder->addQuery(new \Elastica\Query\Term(['field' => 'value']))
    ->addFilter(new \Elastica\Filter\Term(['field' => 'value']));
    ->setMaxResults(20)
    ->setFirstResults(40);

$tweets = $queryBuilder->getResult(); // return an \Elastica\ResultSet or a \Mapado\Elastica\Model\SearchResult
```

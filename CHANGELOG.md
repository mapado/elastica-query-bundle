CHANGELOG
===================

## 3.0.1 - 2018-08-23

### Changed

* fix issue with data collector template

## 3.0.0 - 2018-08-23

### Added 

* Use phpstan to detect bugs

### Changes

* Changed LICENCE to MIT
* Drop support for PHP < 7.2
* [MIGHT BREAK] Add static typing. Will break if you extend [`Mapado\ElasticaQueryBundle\QueryBuilder`](https://github.com/mapado/elastica-query-bundle/blob/master/src/QueryBuilder.php), but those method should be "internal" to the package.

## 2.0
### Breaking changes
This bundle now require symfony > 2.7, but nothing really changed

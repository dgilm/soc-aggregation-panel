soc-aggregation-panel
=====================

An aggregation application for SOCs (Security Operation Center) management

# Requirements

client:

  * lsb-release
  * python deps:
    + pika: Python AMQP Client Library
    + simplejson: JSON encoder/decoder for Python
    + yaml: YAML parser and emitter for Python
    + fabric: Simple Pythonic remote deployment tool
    + psutil: module providing convenience functions for managing processes
    + geoip: GeoIP IP-to-country resolver library

server:

   * rabbitmq-server
   * mongo database server
   * python deps:
    + pika: Python AMQP Client Library
    + simplejson: JSON encoder/decoder for Python
    + yaml: YAML parser and emitter for Python
    + pymongo: Python interface to the MongoDB document-oriented database

gis:

   * bootstrap css (online)
   * mongodb: mongo database engine for php
   * php5 + apache2:
     + apt-get install php5 apache2 php5-dev php-pear
     + pecl install mongo
     + php.ini -> Dynamic extensions: add "extension=mongo.so"


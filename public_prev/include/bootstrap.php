<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 15-01-17
 * Time: 11:50
 */


require_once __DIR__ . '/../classes/Settings.php';

Settings::setPublic('solr', array(
    'endpoint' => array(
        'localhost' => array(
            'host' => 'solr',
            'port' => 8983,
            'path' => '/solr/vici/',
        )
    )
));

Settings::setPublic('solr', array(
    'endpoint' => array(
        'localhost' => array(
            'host' => 'localhost',
            'port' => 8983,
            'path' => '/solr/vici/',
        )
    )
));

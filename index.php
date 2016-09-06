<?php

use Dgame\Hydrator\ArrayHydrator;
use Dgame\Hydrator\Resolver;
use Dgame\Hydrator\XmlHydrator;

require_once 'vendor/autoload.php';

$doc = new DOMDocument('1.0', 'utf-8');
$doc->load('test/xml/test.xml');

$resolver = new Resolver();

print '<pre>';
$hydrator = new XmlHydrator($doc, $resolver);
print_r($hydrator->getHydratedObjects());

$data = [
    'Lieferung' => [
        'id'       => 123,
        'Transfer' => [
            'Erstelldatum' => '15.07.1987'
        ]
    ]
];

$hydrator = new ArrayHydrator($data, $resolver);
print_r($hydrator->getHydratedObjects());
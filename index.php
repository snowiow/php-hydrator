<?php

use Dgame\Hydrator\ArrayHydrator;
use Dgame\Hydrator\JsonHydrator;
use Dgame\Hydrator\Resolver;
use Dgame\Hydrator\XmlHydrator;

require_once 'vendor/autoload.php';

print '<pre>';

$doc = new DOMDocument('1.0', 'utf-8');
$doc->load('tests/xml/nested.xml');

Resolver::new()->appendNamespace('Nested');

$hydrator = new XmlHydrator();
$hydrator->hydrate($doc);
print_r($hydrator->getHydratedObjects());

exit;

$doc = new DOMDocument('1.0', 'utf-8');
$doc->load('tests/xml/test.xml');

Resolver::new()->appendNamespace('Bipro')->enableMagic()->useAlias('Proxy')->for('Event');

$hydrator = new XmlHydrator();
$hydrator->hydrate($doc);
print_r($hydrator->getHydratedObjects());

exit;
$data = [
    'Lieferung' => [
        'id' => 123,
        [
            'Transfer' => [
                'Erstelldatum' => '11.11.1911',
                [
                    'Datei' => [
                        'Dateiname' => 'abc.pdf'
                    ]
                ],
                [
                    'Datei' => [
                        'Dateiname' => 'def.zip'
                    ]
                ]
            ]
        ],
        [
            'Transfer' => [
                'Erstelldatum' => '14.04.1921',
                [
                    'Person' => [
                        'type' => 'Partner',
                        'name' => 'Max Musterman'
                    ]
                ]
            ]
        ]
    ]
];

$hydrator = new ArrayHydrator($resolver);
$hydrator->hydrate($data);
//print_r($hydrator->getHydratedObjects());

$json = file_get_contents('tests/json/test.json');

$hydrator = new JsonHydrator($resolver);
$hydrator->hydrate(Lieferung::class, json_decode($json, true));
print_r($hydrator->getHydratedObjects());
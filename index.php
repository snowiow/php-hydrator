<?php

use Dgame\Hydrator\ArrayHydrator;
use Dgame\Hydrator\JsonHydrator;
use Dgame\Hydrator\Resolver;
use Dgame\Hydrator\XmlHydrator;

require_once 'vendor/autoload.php';

$doc = new DOMDocument('1.0', 'utf-8');
$doc->load('tests/xml/test.xml');

$resolver = new Resolver();

print '<pre>';
$hydrator = new XmlHydrator($resolver);
$hydrator->hydrate($doc);
//print_r($hydrator->getHydratedObjects());

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
<?php

use Dgame\Hydrator\XmlHydrator;

require_once 'vendor/autoload.php';

$doc = new DOMDocument('1.0', 'utf-8');
$doc->load('test/xml/test.xml');

//$resolver = new Resolver();

print '<pre>';
$hydrator = new XmlHydrator();
$hydrator->hydrate($doc);
print_r($hydrator->getHydratedObjects());

//$data = [
//    'Lieferung' => [
//        'id' => 123,
//        [
//            'Transfer' => [
//                'Erstelldatum' => '11.11.1911',
//                ['Datei' => ['Dateiname' => 'abc.pdf']],
//                ['Datei' => ['Dateiname' => 'def.zip']]
//            ],
//        ],
//        [
//            'Transfer' => [
//                'Erstelldatum' => '14.04.1921',
//                [
//                    'Person' => [
//                        'type' => 'Partner',
//                        'name' => 'Max Musterman'
//                    ]
//                ]
//            ]
//        ]
//    ]
//];
//
//$hydrator = new ArrayHydrator($resolver);
//$hydrator->hydrate($data);
//print_r($hydrator->getHydratedObjects());
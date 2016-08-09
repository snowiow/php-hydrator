<?php

use Dgame\ObjectMapper\ObjectHydrate;
use Dgame\ObjectMapper\XmlObjectHydrate;
use Dgame\ObjectMapper\XmlObjectMapper;

require_once '../vendor/autoload.php';

$doc = new DOMDocument('1.0', 'utf-8');
$doc->load('test.xml');

$xoh = new XmlObjectHydrate(function(string $class) {
    $filename = getcwd() . '/' . $class . '.php';
    if (file_exists($filename)) {
        require_once $filename;

        return true;
    }

    return false;
});

print '<pre>';
$mapper =  new XmlObjectMapper($doc);
print_r($mapper->getAttributes());
exit;
var_dump($xoh->hydrate($doc));

//$attrs = $xoh->hydrate($doc);
//$oh = new ObjectHydrate(Lieferung::class);
//
//print_r($oh->hydrate($attrs['s:Envelope']['s:Body']['getShipmentResponse']['Response']['Lieferung']));

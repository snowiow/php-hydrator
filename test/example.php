<?php

use Dgame\ObjectMapper\Xml\NodeProcessor;

require_once '../vendor/autoload.php';

print '<pre>';

$doc = new DOMDocument('1.0', 'utf-8');
$doc->load('xml/get_response.xml');

$np = new NodeProcessor($doc);
print_r($np->getOutput());

<?php

use Dgame\Hydrator\Resolver;
use Dgame\Hydrator\XmlHydrator;
use PHPUnit\Framework\TestCase;

require_once '../vendor/autoload.php';

class XmlHydrationTest extends TestCase
{
    public function testXmlHydration()
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->load('xml/test.xml');

        $hydrator = new XmlHydrator(new Resolver());

        $hydrator->hydrate($doc);
        $objects = $hydrator->getHydratedObjects();

        $this->assertInstanceOf(Status::class, $objects[0]);
        $this->assertInstanceOf(Meldung::class, $objects[1]);
        $this->assertInstanceOf(Lieferung::class, $objects[2]);
        $this->assertInstanceOf(Transfer::class, $objects[3]);
        $this->assertInstanceOf(Datei::class, $objects[4]);
        $this->assertInstanceOf(Datei::class, $objects[5]);
        $this->assertInstanceOf(Transfer::class, $objects[6]);

        $this->assertInstanceOf(Meldung::class, $objects[0]->Meldung);
        $this->assertCount(2, $objects[2]->getTransfers());
        foreach ($objects[2]->getTransfers() as $transfer) {
            $this->assertInstanceOf(Transfer::class, $transfer);
        }
        $this->assertCount(2, $objects[2]->getTransfers()[0]->getFiles());
        foreach ($objects[2]->getTransfers()[0]->getFiles() as $file) {
            $this->assertInstanceOf(Datei::class, $file);
        }

        $this->assertEquals(753, $objects[0]->ProzessID);
        $this->assertEquals('Awesome', $objects[0]->Meldung->Text);
        $this->assertSame($objects[0]->Meldung, $objects[1]);
        $this->assertEquals('Awesome', $objects[1]->Text);

        $this->assertEquals(1337, $objects[2]->getId());
        $this->assertEquals(123, $objects[2]->getVUNummer());
        $this->assertEquals('Test', $objects[2]->getCategory());

        $this->assertEquals('11.11.1911', $objects[3]->getErstelldatum());
        $this->assertEquals('14.04.1921', $objects[6]->getErstelldatum());
        $this->assertEquals('abc.pdf', $objects[4]->Dateiname);
        $this->assertEquals('def.zip', $objects[5]->Dateiname);

        $this->assertInstanceOf(Transfer::class, $objects[6]);
        $this->assertInstanceOf(Person::class, $objects[6]->getPerson());
        $this->assertEquals('Partner', $objects[6]->getPerson()->type);
        $this->assertEquals('Max Musterman', $objects[6]->getPerson()->getName());
        $this->assertInstanceOf(Person::class, $objects[7]);
        $this->assertSame($objects[7], $objects[6]->getPerson());
    }
}
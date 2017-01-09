<?php

use Bipro\Datei;
use Bipro\Lieferung;
use Bipro\Meldung;
use Bipro\Person;
use Bipro\Proxy;
use Bipro\Status;
use Bipro\Transfer;
use Dgame\Hydrator\Resolver;
use Dgame\Hydrator\XmlHydrator;
use Nested\Complex;
use Nested\Schema;
use Nested\Sequence;
use PHPUnit\Framework\TestCase;

class XmlHydrationTest extends TestCase
{
    public function testBiproXmlHydration()
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->load(dirname(__FILE__) . '/xml/test.xml');

        Resolver::new()->appendNamespace('Bipro')->enableMagic()->useAlias('Proxy')->for('Event');

        $hydrator = new XmlHydrator();

        $hydrator->hydrate($doc);
        $objects = $hydrator->getHydratedObjects();

        $this->assertInstanceOf(Status::class, $objects[0]);
        $this->assertInstanceOf(Meldung::class, $objects[1]);
        $this->assertInstanceOf(Lieferung::class, $objects[2]);
        $this->assertInstanceOf(Transfer::class, $objects[3]);
        $this->assertInstanceOf(Datei::class, $objects[4]);
        $this->assertInstanceOf(Datei::class, $objects[5]);
        $this->assertInstanceOf(Transfer::class, $objects[6]);
        $this->assertInstanceOf(Person::class, $objects[7]);
        $this->assertInstanceOf(Proxy::class, $objects[8]);

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

        $this->assertEquals('Foo', $objects[8]->name);
        $this->assertEquals(200, $objects[8]->code);
    }

    public function testNestedXmlHydration()
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->load(dirname(__FILE__) . '/xml/nested.xml');

        Resolver::new()->appendNamespace('Nested');

        $hydrator = new XmlHydrator();
        $hydrator->hydrate($doc);
        $objects = $hydrator->getHydratedObjects();

        $this->assertEquals(7, count($objects));
        $this->assertInstanceOf(Schema::class, $objects[0]);
        $this->assertInstanceOf(Complex::class, $objects[0]->complex);
        $this->assertInstanceOf(Sequence::class, $objects[0]->complex->sequence);
        $this->assertEquals('A1', $objects[0]->complex->sequence->getElements()[0]->name);
        $this->assertEquals('A2', $objects[0]->complex->sequence->getElements()[1]->name);
        $this->assertEquals('B1', $objects[0]->complex->getElements()[0]->name);
        $this->assertEquals('C1', $objects[0]->getElements()[0]->name);
    }
}
<?php

use Dgame\Hydrator\JsonHydrator;
use Dgame\Hydrator\Resolver;
use PHPUnit\Framework\TestCase;

class JsonHydrationTest extends TestCase
{
    public function testJsonHydration()
    {
        $json = file_get_contents('json/test.json');

        $resolver = new Resolver();
        $hydrator = new JsonHydrator($resolver);
        $hydrator->hydrate(Lieferung::class, json_decode($json, true));

        $objects = $hydrator->getHydratedObjects();

        $this->assertInstanceOf(Lieferung::class, $objects[0]);
        $this->assertInstanceOf(Transfer::class, $objects[1]);

        $this->assertEquals(123, $objects[0]->getId());
        $this->assertCount(2, $objects[0]->getTransfers());
        foreach ($objects[0]->getTransfers() as $transfer) {
            $this->assertInstanceOf(Transfer::class, $transfer);
        }

        $this->assertCount(2, $objects[0]->getTransfers()[0]->getFiles());
        foreach ($objects[0]->getTransfers()[0]->getFiles() as $file) {
            $this->assertInstanceOf(Datei::class, $file);
        }

        $this->assertEquals('11.11.1911', $objects[1]->getErstelldatum());
        $this->assertEquals('14.04.1921', $objects[4]->getErstelldatum());
        $this->assertEquals('abc.pdf', $objects[2]->Dateiname);
        $this->assertEquals('def.zip', $objects[3]->Dateiname);

        $this->assertInstanceOf(Transfer::class, $objects[4]);
        $this->assertInstanceOf(Person::class, $objects[4]->getPerson());
        $this->assertEquals('Partner', $objects[4]->getPerson()->type);
        $this->assertEquals('Max Musterman', $objects[4]->getPerson()->getName());
        $this->assertInstanceOf(Person::class, $objects[5]);
        $this->assertSame($objects[5], $objects[4]->getPerson());
    }
}
<?php

use Dgame\Hydrator\ArrayHydrator;
use Dgame\Hydrator\Resolver;
use PHPUnit\Framework\TestCase;

class ArrayHydrationTest extends TestCase
{
    public function testArrayHydration()
    {
        $data = [
            'Lieferung' => [
                'id' => 123,
                [
                    'Transfer' => [
                        'Erstelldatum' => '11.11.1911',
                        ['Datei' => ['Dateiname' => 'abc.pdf']],
                        ['Datei' => ['Dateiname' => 'def.zip']]
                    ],
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

        $resolver = new Resolver();
        $hydrator = new ArrayHydrator($resolver);

        $hydrator->hydrate($data);
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
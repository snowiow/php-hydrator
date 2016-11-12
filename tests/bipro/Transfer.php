<?php

namespace Bipro;

class Transfer
{
    private $Erstelldatum;
    private $files = [];
    private $person;

    public function setErstelldatum(string $Erstelldatum)
    {
        $this->Erstelldatum = $Erstelldatum;
    }

    public function appendDatei(Datei $datei)
    {
        $this->files[] = $datei;
    }

    public function setPerson(Person $person)
    {
        $this->person = $person;
    }

    public function getErstelldatum(): string
    {
        return $this->Erstelldatum;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getPerson(): Person
    {
        return $this->person;
    }
}
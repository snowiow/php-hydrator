<?php

class Transfer
{
    private $Erstelldatum;
    private $files = [];

    public function setErstelldatum(string $Erstelldatum)
    {
        $this->Erstelldatum = $Erstelldatum;
    }

    public function appendDatei(Datei $datei)
    {
        $this->files[] = $datei;
    }

    public function getErstelldatum(): string
    {
        return $this->Erstelldatum;
    }

    public function getFiles(): array
    {
        return $this->files;
    }
}
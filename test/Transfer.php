<?php

class Transfer
{
    private $Erstelldatum = null;
    private $files        = [];

    public function setErstelldatum(string $Erstelldatum)
    {
        $this->Erstelldatum = $Erstelldatum;
    }

    public function appendDatei(Datei $datei)
    {
        $this->files[] = $datei;
    }
}
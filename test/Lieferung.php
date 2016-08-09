<?php

class Lieferung
{
    private $id       = null;
    private $VUNummer = null;
    private $category = null;
    private $transfer = null;

    public function setID(string $id)
    {
        $this->id = $id;
    }

    public function setVUNummer(string $VUNummer)
    {
        $this->VUNummer = $VUNummer;
    }

    public function setKategorie(string $category)
    {
        $this->category = $category;
    }

    public function setTransfer(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }
}
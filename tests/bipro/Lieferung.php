<?php

namespace Bipro;

class Lieferung
{
    private $id;
    private $VUNummer;
    private $category;
    private $transfers = [];

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

    public function appendTransfer(Transfer $transfer)
    {
        $this->transfers[] = $transfer;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getVUNummer()
    {
        return $this->VUNummer;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getTransfers(): array
    {
        return $this->transfers;
    }
}
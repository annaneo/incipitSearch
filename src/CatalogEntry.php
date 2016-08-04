<?php

namespace ADWLM\IncipitSearch;

use ADWLM\IncipitSearch\Incipit;

/**
 * The CatalogEntry class represents a database entry for the incipit collection.
 *
 * It encapsulates an Incipit and all relevant meta information required for
 * search and retrieval. It provides JSON encoding function to directly
 * add the CatalogEntry to a JSON-base database (like ElasticSearch)
 *
 *
 * Copyright notice
 *
 * (c) 2016
 * Anna Neovesky  Anna.Neovesky@adwmainz.de
 * Gabriel Reimers g.a.reimers@gmail.com
 *
 * Digital Academy www.digitale-akademie.de
 * Academy of Sciences and Literatur | Mainz www.adwmainz.de
 *
 * Licensed under The MIT License (MIT)
 *
 * @package ADWLM\IncipitSearch
 */
class CatalogEntry
{

    protected $catalog;
    protected $catalogItemID;
    protected $dataURL;
    protected $detailURL;
    protected $incipit;
    protected $composer;
    protected $title;
    protected $year;

    /**
     * CatalogEntry constructor.
     * @param Incipit|null $incipit
     * @param string|null $catalog
     * @param string|null $catalogItemID
     * @param string|null $dataURL
     * @param string|null $detailURL
     * @param string|null $composer
     * @param string|null $title
     * @param string|null $year
     */
    public function __construct(Incipit $incipit = null, string $catalog = null, string $catalogItemID = null,
                                string $dataURL = null, string $detailURL = null,
                                string $composer = null, string $title = null, string $year = null)
    {
        if ($incipit == null) {
            echo "CatalogEntry > construct > incipit is null";
        }
        if ($catalogItemID == null) {
            echo "CatalogEntry > construct > catalogItemID is null";

        }
        //TODO: catch invalid data
        $this->incipit = $incipit;
        $this->catalog = $catalog ?? "";
        $this->catalogItemID = $catalogItemID ?? "";
        $this->dataURL = $dataURL ?? "";
        $this->detailURL = $detailURL ?? "";
        $this->composer = $composer ?? "";
        $this->title = $title ?? "";
        $this->year = $year ?? "";

    }


    /**
     * Parses object CatalogEntry to json representation (assoc array)
     * @return mixed
     */
    public function getJSONArray(): array {
        $array = ['catalog' => $this->getCatalog(),
            'catalogItemID' => $this->getCatalogItemID(),
            'dataURL' => $this->getDataURL(),
            'detailURL' => $this->getDetailURL(),
            'incipit' => $this->getIncipit()->getJSONArray(),
            'composer' => $this->getComposer(),
            'title' => $this->getTitle(),
            'year' => $this->getYear()
        ];
        return $array;
    }

    /**
     * Creates incipit entry from json representation
     * @param array $jsonArray
     * @return CatalogEntry
     */
    public static function catalogEntryFromJSONArray(array $jsonArray): CatalogEntry
    {
        $incipit = Incipit::incipitFromJSONArray($jsonArray["incipit"]);
        $catalogEntry = new CatalogEntry($incipit, $jsonArray["catalog"],
            $jsonArray["catalogItemID"],
            $jsonArray["dataURL"],
            $jsonArray["detailURL"], $jsonArray["composer"], $jsonArray["title"], $jsonArray["year"]);
        return $catalogEntry;
    }

    public function getJSONString(): string
    {
        $array = $this->getJSONArray();
        $json = json_encode($array);
        return $json;
    }





    ///////////////////////
    // GETTERS
    ///////////////////////

    /**
     * @return string
     */
    public function getCatalog(): string
    {
        return $this->catalog;
    }

    /**
     * Gets ID of catalog
     * @return string
     */
    public function getCatalogItemID(): string
    {
        return $this->catalogItemID;
    }

    /**
     * Gets URL for data output (XML, RDF...)
     * @return string
     */
    public function getDataURL(): string
    {
        return $this->dataURL;
    }

    /**
     * Gets url for representation of irem in catalog
     * @return string
     */
    public function getDetailURL(): string
    {
        return $this->detailURL;
    }



    /**
     * Gets name of composer
     * @return string
     */
    public function getComposer(): string
    {
        return $this->composer;
    }

    /**
     * Gets title of work
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Gets year of composition
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * Get sincipit
     * @return Incipit
     */
    public function getIncipit()
    {
        return $this->incipit;
    }
}

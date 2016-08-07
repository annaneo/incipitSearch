<?php
namespace ADWLM\IncipitSearch;

/**
 * Created by PhpStorm.
 * User: gaby
 * Date: 29/06/16
 * Time: 10:39
 */


use SimpleXMLElement;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Elasticsearch\ClientBuilder;

use ADWLM\IncipitSearch\Incipit;
use ADWLM\IncipitSearch\CatalogEntry;

class GluckIncipitCrawler extends IncipitCrawler
{

    protected $logs = [];

    private function addLog(string $message) {
        array_push($this->logs, $message);
    }

    /**
     * Creates a CatalogEntry with Incipit from the data at the given URL.
     *
     * @param string $dataURL url of data in catalog
     * @return CatalogEntry null in case of error
     */
    public function catalogEntryFromWork(string $dataURL) //can return null
    {

        $xml = $this->contentOfURL($dataURL);

        if ($xml == null || strlen($xml) == 0) {
            $this->addLog("error: catalogEntryFromWork > not found at {$dataURL}");
            return;
        }
        try {
            $parentXMLElement = new SimpleXMLElement($xml);
        } catch (\Exception $e) {
            // Handle all other exceptions
            $this->addLog("error: catalogEntryFromXML at {$dataURL} > could not parse XML > {$e->getMessage()}");
            return null;
        }

        $work = $parentXMLElement->xpath("/rdf:RDF/skos:Concept")[0];
        $workIdentifier = $this->contentOfXMLPath($work, "dc:identifier");
        $workTitle = $this->contentOfXMLPath($work, "dc:title");
        $workDetailUrl = $work->attributes()["about"];

        $parts = $parentXMLElement->xpath("/rdf:RDF/skos:Concept/skos:relatedMatch/skos:Concept");
        foreach ($parts as $part) {
            $partTitle = $this->contentOfXMLPath($part, "dc:title");

            $incipitClef = $this->contentOfXMLPath($part,
                "skos:relatedMatch/skos:Concept/bsbmo:incipitClef");
            $incipitAccidentals = $this->contentOfXMLPath($part, "skos:relatedMatch/skos:Concept/bsbmo:incipitKeysig");
            $incipitTime = $this->contentOfXMLPath($part, "skos:relatedMatch/skos:Concept/bsbmo:incipitTimesig");
            $incipitNotes = $this->contentOfXMLPath($part, "skos:relatedMatch/skos:Concept/bsbmo:incipitScore");
            $composer = "Christoph Willibald Gluck";
            $this->addLog("catalogEntryFromWork > $workTitle $partTitle\n" .
                "$incipitClef $incipitAccidentals $incipitTime $incipitNotes");


            $fullTitle = $workTitle . " " . $partTitle;

            $incipit = new Incipit($incipitNotes, $incipitClef, $incipitAccidentals, $incipitTime);
            $catalogEntry = new CatalogEntry($incipit, "Gluck-Gesamtausgabe", $workIdentifier, $dataURL, $workDetailUrl,
                $composer, $fullTitle, "");

            return $catalogEntry;
        }
    }

    /**
     * Extracts the string content of an XML element at the given xpath.
     * @param SimpleXMLElement $parentXmlElement
     * @param string $xpath
     * @return string the content, empty if not found
     */
    private function contentOfXMLPath(SimpleXMLElement $parentXmlElement, string $xpath): string
    {
        if ($parentXmlElement == null) {
            $this->addLog("error: contentOfXMLElementAtPath > no parentXmlElement given");
            return "";
        }
        $matchingElements = $parentXmlElement->xpath($xpath);
        if ($matchingElements == null || empty($matchingElements)) {
            return "";
        }
        return (string)$matchingElements[0];
    }


    /**
     * Crawls catalog and adds found CatalogEntries to ElasticSearch.
     * This operation might take quite some time to complete.
     */
    public function crawlCatalog()
    {


        $url = "http://gluck-gesamtausgabe.local/rdf/collection/works/";
        $xml = $this->contentOfURL($url);

        if ($xml == null || strlen($xml) == 0) {
            array_push($this->logs, "error: crawlCatalog > not found at {$url}");
            return;
        }
        $this->addLog("read index xml: \n\n {$xml}");


        try {
            $parentXMLElement = new SimpleXMLElement($xml);
        } catch (\Exception $e) {
            // Handle all other exceptions
            $this->addLog("error: catalogEntryFromXML at $url > could not parse XML > {$e->getMessage()}");
            return null;
        }

        $matchingElements = $parentXMLElement->xpath("/collection/resources/resource");
        foreach ($matchingElements as $resource) {
            $title = (string)$resource->xpath("title")[0];
            $workUrl = (string)$resource->attributes()["target"];
            $this->addLog("work: $title $workUrl ");
            $catalogEntry = $this->catalogEntryFromWork($workUrl);
            $this->addCatalogEntryToElasticSearchIndex($catalogEntry);
        }


    }

    /**
     * Returns an array of log entries generated during crawling.
     *
     * @return array of strings (log entries)
     */
    public function getLogs(): array
    {
        return $this->logs;
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: gaby
 * Date: 20/04/16
 * Time: 12:27
 */

namespace ADWLM\IncipitSearch;


class Incipit
{


    protected $key;
    protected $accidentals;
    protected $time;
    protected $notes;
    protected $completeIncipit;
    protected $notesNormalized;

    /**
     * Incipit constructor.
     * @param string $notes
     * @param string|null $key
     * @param string|null $accidentals
     * @param string|null $time
     */
    public function __construct(string $notes, string $key = null,
                                string $accidentals = null, string $time = null)
    {
        $this->notes = $notes;
        $this->key = $key ?? "";
        $this->accidentals = $accidentals ?? "";
        $this->time = $time ?? "";
    }

    /**
     * COmbines key, accidentals, time and notes in one string
     * @return mixed
     */
    public function getCompleteIncipit(): string
    {
        if (empty($this->completeIncipit)) {
            $this->completeIncipit = $this->key . $this->accidentals .
                $this->time . $this->notes;
        }
        return $this->completeIncipit;
    }

//TODO: check if and what kind of normalization is necessary
    /**
     * Normalizes incipit for use in search: removes tone pitch and accidentals
     * @return string
     */
    public function getNotesNormalized(): string
    {
        if (empty($this->notesNormalized)) {
            $notes = $this->notes;
            $this->notesNormalized = preg_replace('/[^a-zA-Z]/', '', $notes);
        }
        return $this->notesNormalized;
    }


    /**
     * Creates json array
     * @return mixed
     */
    public function getJSONArray(): Array {
        $json = ['notes' => $this->getNotes(),
            'key' => $this->getKey(),
            'accidentals' => $this->getAccidentals(),
            'time' => $this->getTime(),
            'completeIncipit' => $this->getCompleteIncipit(),
            'normalizedIncipit' => $this->getNotesNormalized()
        ];
        return $json;
    }

    /**
     * @param array $json
     * @return Incipit
     */
    public static function incipitFromJSONArray(array $json): Incipit {
        $incipit = new Incipit( $json["notes"],$json["key"], $json["accidentals"],$json["time"]);
        return $incipit;
    }




    //////////////////////////
    // GETTERS
    //////////////////////////

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getAccidentals()
    {
        return $this->accidentals;
    }

    /**
     * The time signature.
     *
     * This is usually a fraction like '2/4' but can also be 'c' for commom time.
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
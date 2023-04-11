<?php

namespace Vioms\Cities\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    /**
     * Will load all cities from json file
     */
    const LOAD_FILE = 1;
    /**
     * Will load all cities from cache
     */
    const LOAD_CACHE = 2;
    /**
     * Will load all cities from database
     */
    const LOAD_DB = 3;

    protected $cities = [];

    protected $table;

    public $timestamps = false;

    protected $casts = [
        'alternate_names' => 'json'
    ];

    protected $fillable = [
        'id',
        'name',
        'ascii_name',
        'alternate_names',
        'latitude',
        'longitude',
        'feature_class',
        'feature_code',
        'country_id',
        'population',
        'elevation',
        'timezone',
        'updated_at',
    ];

    public function __construct()
    {
        $this->table = config('cities.table', 'cities');
        parent::__construct();
    }


    public function getOne($idOrCode, int $id): array
    {
        return $this->fetchCities($idOrCode)[$id];
    }

    /**
     * Returns a list of cities
     *
     * @param string sort
     *
     * @return array
     */
    public function getList($idOrCode, $sort = null): array
    {
        //Get the cities list
        $cities = $this->fetchCities($idOrCode);

        //Sorting
        $validSorts = [
            'name',
            'ascii_name',
            'population',
        ];

        if (!is_null($sort) && in_array($sort, $validSorts)) {
            uasort($cities, function ($a, $b) use ($sort) {
                if ($a instanceof Model) {
                    $a = $a->toArray();
                }
                if ($b instanceof Model) {
                    $b = $b->toArray();
                }
                if (!isset($a[$sort]) && !isset($b[$sort])) {
                    return 0;
                } elseif (!isset($a[$sort])) {
                    return -1;
                } elseif (!isset($b[$sort])) {
                    return 1;
                } else {
                    return strcasecmp($a[$sort], $b[$sort]);
                }
            });
        }

        //Return the cities
        return $cities;
    }

    public function getAllFiles()
    {
        $cityFiles = glob(__DIR__, '../../resources/dataset/*.json');
        $collection = [];
        foreach ($cityFiles as $filePath) {
            $collection = array_merge($collection, json_decode(file_get_contents($filePath), true));
        }
        return $collection;
    }


    /**
     * Returns a list of cities suitable to use with a select element in LaravelCollective\html
     * Will show the value and sort by the column specified in the display attribute
     * @param string $display key to display in select default: name
     *
     * @return array
     */
    public function getListForSelect($idOrCode, $display = 'name'): array
    {
        $cities = [];
        foreach ($this->getList($idOrCode, $display) as $key => $value) {
            $cities[$key] = $value[$display];
        }
        return $cities;
    }

    /**
     * Get all cities
     * @return array|mixed
     */
    protected function fetchCities($idOrCode)
    {
        if (!empty($this->cities)) {
            return $this->cities;
        }

        $type = config('cities.cache', self::LOAD_FILE);
        if ($type === self::LOAD_CACHE && \Cache::has(__FUNCTION__ . $idOrCode)) {
            return $this->cities = \Cache::get(__FUNCTION__ . $idOrCode);
        } elseif ($type === self::LOAD_DB) {
            if (is_numeric($idOrCode)) {
                return $this->cities = $this->where('country_id', $idOrCode)->get()->pluck(null, 'id')->toArray();
            }
            $country = Country::where('iso_3166_2', $idOrCode)->firstOrFail();
            return $this->cities = $this->where('country_id', $country->id)->get()->pluck(null, 'id')->toArray();
        }
        if (is_numeric($idOrCode)) {
            $citiesFile = glob(__DIR__, '../../resources/dataset/' . $idOrCode . '-*-cities.json');
        } else {
            $citiesFile = glob(__DIR__, '../../resources/dataset/*-' . strtoupper($idOrCode) . '-cities.json');
        }
        $this->cities = json_decode(file_get_contents($citiesFile[0]), true);
        if ($type === self::LOAD_CACHE) {
            \Cache::put(__FUNCTION__ . $idOrCode, $this->cities);
        }
        return $this->cities;
    }
}

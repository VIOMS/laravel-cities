<?php

namespace Vioms\Cities\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id Unique identifier
 * @property string $name Name of the city
 * @property string $ascii_name Name of the city in ascii
 * @property array $alternate_names Alternative names for the cities
 * @property float $latitude Latitude of the city
 * @property float $longitude Longitude of the city
 * @property string $feature_class feature_class of the city See: http://www.geonames.org/export/codes.html
 * @property string $feature_code feature code of the city See: http://www.geonames.org/export/codes.html
 * @property integer $population population of the city
 * @property integer $elevation elevation of the city
 * @property string $timezone in which timezone the city is located
 * @property Carbon $updated_at last updated
 */
class City extends Model
{

    protected $table;

    public $timestamps = false;

    protected $dates = ['updated_at'];

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

    /**
     * Return an array that can be used to build a select element.
     * @param $countryId
     * @param $display
     * @return array
     */
    public function getSelectList($countryId, $display='ascii_name'): array
    {
        return $this->query()->where('country_id', $countryId)->get(['id', $display])->pluck($display, 'id')->toArray();
    }
}

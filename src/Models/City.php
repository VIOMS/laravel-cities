<?php

namespace Vioms\Cities\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{

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

    public function getSelectList($countryId, $display='ascii_name'): array
    {
        return $this->query()->where('country_id', $countryId)->get(['id', $display])->pluck($display, 'id')->toArray();
    }
}

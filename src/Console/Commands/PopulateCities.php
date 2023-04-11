<?php

namespace Vioms\Cities\Console\Commands;

use Illuminate\Console\Command;
use Vioms\Cities\Models\City;
use Vioms\Countries\Models\Country;

class PopulateCities extends Command
{
    const ZIP_LOCATION = 'allCountries.zip';
    const CSV_LOCATION = 'allCountries.txt';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cities:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will download and populate the cities';

    protected $firstImport = false;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!\Schema::hasTable(config('cities.table', 'cities'))) {
            $this->error('The cities tables doesn\'t exist');
            return Command::FAILURE;
        }
        if (!\Schema::hasTable(config('countries.table', 'countries'))) {
            $this->error('The countries table doesn\'t exist');
            return Command::FAILURE;
        }
        if (Country::count() == 0) {
            $this->error('The countries table is empty');
            return Command::FAILURE;
        }

        $this->firstImport = City::count() === 0;

        $this->download();
        $this->unpack();
        $this->populate();
        $this->cleanup();

        return Command::SUCCESS;
    }

    private function download()
    {
        $this->info('Downloading data');
        $storageResource = fopen(storage_path(self::ZIP_LOCATION), "w+");
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', config('cities.dataset_zip'), ['sink' => $storageResource]);
        fclose($storageResource);
        return ($response->getStatusCode() === 200);
    }

    private function unpack()
    {
        $this->info('Unpacking data');
        $zip = new \ZipArchive();
        $res = $zip->open(storage_path(self::ZIP_LOCATION));
        if ($res === TRUE) {
            $zip->extractTo(storage_path());
            $zip->close();
            return true;
        }

        return false;
    }

    private function populate()
    {
        $this->info('Writing to database');
        $citiesCodeToID = Country::get()->pluck('id', 'iso_3166_2')->toArray();
        $fp = fopen(storage_path(self::CSV_LOCATION), "r");
        $headers = [
            'geonameid',
            'name',
            'asciiname',
            'alternatenames',
            'latitude',
            'longitude',
            'feature class',
            'feature code',
            'country code',
            'cc2',
            'admin1 code',
            'admin2 code',
            'admin3 code',
            'admin4 code',
            'population',
            'elevation',
            'dem',
            'timezone',
            'modification date',
        ];
        $separator = "\t";
        $keysToAdd = [
            'geonameid' => 'id',
            'name' => 'name',
            'asciiname' => 'ascii_name',
            'alternatenames' => 'alternate_names',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'feature class' => 'feature_class',
            'feature code' => 'feature_code',
            'country code' => 'country_id',
            'population' => 'population',
            'elevation' => 'elevation',
            'timezone' => 'timezone',
            'modification date' => 'updated_at',
        ];

        while (($line = fgets($fp)) !== false) {
            if (substr($line, 0, 1) == '#' || empty(trim($line))) {
                // SKIPPING Comment or empty
                $lineBefore = $line;
                continue;
            }
            $data = array_combine($headers, explode($separator, $line));
            $set = [];
            foreach ($keysToAdd as $dataKey => $newKey) {
                switch ($newKey) {
                    case "country_id":
                        $set['country_code'] = $data[$dataKey];
                        if (array_key_exists($data[$dataKey], $citiesCodeToID)) {
                            $set[$newKey] = $citiesCodeToID[$data[$dataKey]];
                        }
                        break;
                    case "alternate_names":
                        $set[$newKey] = array_filter(explode(',', $data[$dataKey]), 'trim');
                        break;
                    case 'latitude':
                    case 'longitude':
                        $set[$newKey] = (float)$data[$dataKey];
                        break;
                    case 'elevation':
                    case 'id':
                    case 'population':
                        $set[$newKey] = (int)$data[$dataKey];
                        break;
                    case "updated_at":
                        $set[$newKey] = $data[$dataKey] . " 00:00:00.000";
                        break;
                    default:
                        $set[$newKey] = $data[$dataKey];
                }
            }
            if (!array_key_exists('country_id', $set)) {
                continue;
            }

            if($this->firstImport) {
                City::create($set);
            }else{
                City::updateOrCreate(['id' => $set['id']], $set);
            }
        }
    }

    private function cleanup()
    {
        $this->info('Cleaning up');
        unlink(storage_path(self::ZIP_LOCATION));
        unlink(storage_path(self::CSV_LOCATION));
    }
}

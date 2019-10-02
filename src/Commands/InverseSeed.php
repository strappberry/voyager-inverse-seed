<?php

namespace Strappberry\VoyagerInverseSeed\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InverseSeed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'strappberry:inverse-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inverses seed all the relevant tables in the database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $first = true;
        $allModels = array_merge(config('voyager-inverse-seed.default_models_to_seed'),
            config('voyager-inverse-seed.custom_models_to_seed'));
        foreach ($allModels as $modelToSeed) {
            if (class_exists($modelToSeed)) {
                $this->line("<comment>Creating seed for:</comment>  {$modelToSeed}");
                $objectToSeed = new $modelToSeed;
                $tableToSeed = $objectToSeed->getTable();
                $this->callSilent('iseed', [
                    'tables' => $tableToSeed,
                    '--clean' => $first,
                    '--dumpauto' => false,
                    '--force' => true,
                    '--exclude' => 'language',
                    '--classnamesuffix' => 'Inverse',
                ]);
                $this->line("<info>Created seed for:</info>   {$modelToSeed}");
            } else {
                $this->line("<comment>Creating seed for:</comment>  {$modelToSeed}");
                $tableToSeed = $modelToSeed;
                $this->callSilent('iseed', [
                    'tables' => $tableToSeed,
                    '--clean' => $first,
                    '--dumpauto' => false,
                    '--force' => true,
                    '--exclude' => 'language',
                    '--classnamesuffix' => 'Inverse',
                ]);
                $this->line("<info>Created seed for:</info>   {$modelToSeed}");
            }
            $first = false;
        }
        $inverseSeedZipFileExists = $this->saveFilesInStorage();
        $this->fixSeederPath($inverseSeedZipFileExists);
    }

    /**
     * This will save all files in the Voyager storage disk to a zip file
     *
     * @return boolean
     * @throws \Exception
     */
    private function saveFilesInStorage()
    {
        $full_zip_path = storage_path("inverseSeed.zip");
        if (File::exists($full_zip_path)) {
            File::delete($full_zip_path);
        }
        $zip = \Zip::create($full_zip_path);
        $voyager_disk = Storage::disk(config('voyager.storage.disk'));
        $voyager_disk_path = $voyager_disk->getDriver()->getAdapter()->getPathPrefix();
        $zip->add($voyager_disk_path);
        $zip->close();

        return File::exists($full_zip_path);
    }

    /**
     * It fixes the seeder file in order to work with our custom seeds
     *
     * @param  boolean  $includeZipFile
     * @throws FileNotFoundException
     */
    private function fixSeederPath($includeZipFile)
    {
        $this->comment("Fixing DatabaseSeeder for inverse seeds");
        $databaseSeederPath = base_path().config('iseed::config.path').'/DatabaseSeeder.php';
        $content = File::get($databaseSeederPath);
        preg_match("/\#iseed_start(.*?)\#iseed_end/us", $content, $matches);
        $fixedData = $matches[0];
        if ($includeZipFile) {
            $unzipCommands = <<< 'UNZIP'
$full_zip_path = storage_path("inverseSeed.zip");
        $voyager_disk = \Storage::disk(config('voyager.storage.disk'));
        $voyager_disk_path = $voyager_disk->getDriver()->getAdapter()->getPathPrefix();
        if (!\File::exists($full_zip_path)) {
            return;
        }
        $zip = \Zip::open($full_zip_path);
        $zip->extract("{$voyager_disk_path}../");
        #iseed_end
UNZIP;
            $fixedData = preg_replace("/\#iseed_end/us", $unzipCommands, $fixedData);
        }
        $newSeederContent = preg_replace("/\#iseed_start(.*?)\#iseed_end/us", $fixedData, $content);
        File::put($databaseSeederPath, $newSeederContent);
        $this->info("Fixed DatabaseSeeder for inverse seeds");
    }
}

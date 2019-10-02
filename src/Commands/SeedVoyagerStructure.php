<?php

namespace Strappberry\VoyagerInverseSeed\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class SeedVoyagerStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'strappberry:seed-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the Voyager relevant tables seeds.';

    protected $seedersPath;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->seedersPath = database_path('seeds/');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        $this->seed('PermissionsTableInverseSeeder');
        $this->seed('RolesTableInverseSeeder');
        $this->seed('PermissionRoleTableInverseSeeder');
        $this->seed('DataTypesTableInverseSeeder');
        $this->seed('DataRowsTableInverseSeeder');
        $this->seed('MenusTableInverseSeeder');
        $this->seed('MenuItemsTableInverseSeeder');
        $this->seed('SettingsTableInverseSeeder');
        $this->seed('TranslationsTableInverseSeeder');
        Schema::enableForeignKeyConstraints();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
    }

    public function seed($class)
    {

        if (!class_exists($class)) {
            require_once $this->seedersPath.$class.'.php';
        }
        $this->line("<comment>Seeding:</comment>   {$class}");
        with(new $class())->run();
        $this->line("<info>Seeded:</info>    {$class}");
    }
}

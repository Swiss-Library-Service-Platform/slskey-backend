<?php

namespace Database\Seeders;

use Database\Seeders\Prod\SlskeyGroupSeeder;
use Database\Seeders\Prod\SwitchGroupAndPublisherSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public const MIGRATE_PRODUCTIVE_DATA = true;
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // User Table Seeder
        $this->call(RolesTableSeeder::class);

        if (self::MIGRATE_PRODUCTIVE_DATA) {
            $this->call(SlskeyGroupSeeder::class);
            $this->call(SwitchGroupAndPublisherSeeder::class);#
        }

        Model::reguard();
    }
}

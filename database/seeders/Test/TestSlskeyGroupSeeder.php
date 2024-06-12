<?php

namespace Database\Seeders\Test;

use App\Enums\WorkflowEnums;
use App\Models\SwitchGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSlskeyGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $man1 = DB::table('slskey_groups')->insertGetId([
            'slskey_code' => 'man1',
            'name' => 'man1',
            'workflow' => WorkflowEnums::MANUAL,
            'alma_iz' => 'doesntmatter',
            'days_activation_duration' => 365,
            'days_expiration_reminder' => 28,
        ]);
        config('roles.models.permission')::create([
            'name' => 'slskey group man1',
            'slug' => 'man1',
            'description' => 'Permission for SLSKey Group man1',
            'model' => 'SLSKeyGroup',
        ]);

        $man2 = DB::table('slskey_groups')->insertGetId([
            'slskey_code' => 'man2',
            'name' => 'man2',
            'alma_iz' => 'doesntmatter',
            'workflow' => WorkflowEnums::MANUAL,
            'days_activation_duration' => 365,
            'days_expiration_reminder' => 28,
        ]);
        config('roles.models.permission')::create([
            'name' => 'slskey group man2',
            'slug' => 'man2',
            'description' => 'Permission for SLSKey Group man2',
            'model' => 'SLSKeyGroup',
        ]);

        /*
        *
        *
        * Webhook Groups
        *
        *
        */

        $webhook1 = DB::table('slskey_groups')->insertGetId([
            'slskey_code' => 'webhook1',
            'name' => 'webhook1',
            'workflow' => WorkflowEnums::WEBHOOK,
            'alma_iz' => '41SLSP_1',
            'webhook_secret' => 'mock_secret',

            'webhook_mail_activation' => true,
            'webhook_mail_activation_domains' => 'example.txt',

            'days_activation_duration' => 365,
            'webhook_mail_activation_days_send_before_expiry' => 14,
            'webhook_mail_activation_days_token_validity' => 60,
        ]);
        config('roles.models.permission')::create([
            'name' => 'slskey group webhook1',
            'slug' => 'webhook1',
            'description' => 'Permission for SLSKey Group webhook1',
            'model' => 'SLSKeyGroup',
        ]);

        $webhook2 = DB::table('slskey_groups')->insertGetId([
            'slskey_code' => 'webhook2',
            'name' => 'webhook2',
            'workflow' => WorkflowEnums::WEBHOOK,
            'alma_iz' => '41SLSP_2',
            'webhook_secret' => 'mock_secret',
        ]);
        config('roles.models.permission')::create([
            'name' => 'slskey group webhook2',
            'slug' => 'webhook2',
            'description' => 'Permission for SLSKey Group webhook2',
            'model' => 'SLSKeyGroup',
        ]);

        $webhook3 = DB::table('slskey_groups')->insertGetId([
            'slskey_code' => 'webhook3',
            'name' => 'webhook3',
            'workflow' => WorkflowEnums::WEBHOOK,
            'alma_iz' => '41SLSP_3',
            'webhook_secret' => 'mock_secret',
            'webhook_custom_verifier' => true,
            'webhook_custom_verifier_class' => 'VerifierABN',
        ]);
        config('roles.models.permission')::create([
            'name' => 'slskey group webhook3',
            'slug' => 'webhook3',
            'description' => 'Permission for SLSKey Group webhook3',
            'model' => 'SLSKeyGroup',
        ]);

        $webhook4 = DB::table('slskey_groups')->insertGetId([
            'slskey_code' => 'webhook4',
            'name' => 'Default Webhook',
            'workflow' => WorkflowEnums::WEBHOOK,
            'alma_iz' => '41SLSP_4',
            'webhook_secret' => 'mock_secret',
            'webhook_custom_verifier' => true,
            'webhook_custom_verifier_class' => 'VerifierA150',
        ]);
        config('roles.models.permission')::create([
            'name' => 'slskey group webhook4',
            'slug' => 'webhook4',
            'description' => 'Permission for SLSKey Group webhook4',
            'model' => 'SLSKeyGroup',
        ]);

        /* SWITCH GROUPS */
        $switchGroup = SwitchGroup::create([
            'name' => 'SLSP Webhooks Testgruppe',
            'switch_group_id' => 'aaa-bbb-ccc',
        ]);
        $switchGroup->slskeyGroups()->attach($man1);
        $switchGroup->slskeyGroups()->attach($man2);
        $switchGroup->slskeyGroups()->attach($webhook1);
        $switchGroup->slskeyGroups()->attach($webhook2);
        $switchGroup->slskeyGroups()->attach($webhook3);
        $switchGroup->slskeyGroups()->attach($webhook4);
        $switchGroup->save();
    }
}

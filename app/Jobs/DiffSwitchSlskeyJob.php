<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Interfaces\SwitchAPIInterface;
use App\Models\SwitchGroup;
use App\Models\SlskeyGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use App\Models\SlskeyUser;
use App\Models\SlskeyActivation;
use App\Models\SlskeyHistory;
use App\Enums\ActivationActionEnums;
use App\Enums\TriggerEnums;

class DiffSwitchSlskeyJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $switchGroupId;
    protected $slskeyCode;
    protected $createUsers;
    protected $timestampedDir;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 86400; // 24 hours

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($switchGroupId, $slskeyCode, $createUsers = false)
    {
        $this->switchGroupId = $switchGroupId;
        $this->slskeyCode = $slskeyCode;
        $this->createUsers = $createUsers;
        $this->timestampedDir = 'diff/' . now()->format('Ymd_His');
        Storage::makeDirectory($this->timestampedDir);
    }

    /**
     * Handle the job
     * Inject dependencies
     *
     * @param SwitchAPIInterface $switchAPIService
     */
    public function handle(SwitchAPIInterface $switchAPIService)
    {
        $switchGroup = SwitchGroup::where('switch_group_id', $this->switchGroupId)->first();
        $slskeyGroup = SlskeyGroup::where('slskey_code', $this->slskeyCode)->first();

        $switchMembersInternalId = $switchAPIService->getMembersForGroupId($switchGroup->switch_group_id);
        $slskeyMembers = $slskeyGroup->getActiveUserPrimaryIds();

        $switchMembersInternalId = collect($switchMembersInternalId);
        $counter = 0;
        $switchMembersExternalId = collect();

        $switchMembersInternalId->take(100)->each(function ($member) use (&$switchMembersExternalId, &$counter) {
            // FIXME: reactivate later
            //$switchMember = $switchAPIService->getSwitchUserInfo($member->value);
            //$switchMembersExternalId->push($switchMember->externalID);

            // Mock data
            $eduID = rand(100000000000, 999999999999) . '@eduid.ch';
            $switchMembersExternalId->push($eduID);

            $counter++;
            if ($counter % 500 == 0) {
                sleep(1); // FIXME: Sleep for 5 seconds after every 10 requests
            }
        });

        $slskeyMembers = collect($slskeyMembers);
        $switchMembers = collect($switchMembersExternalId);

        $slskeyNotInSwitch = $slskeyMembers->diff($switchMembers);
        $switchNotInSlskey = $switchMembers->diff($slskeyMembers);

        // RZS: Optionally add the switch Users to SLSKey that are not in Slskey yet
        $created = [];
        $notEduID = [];
        if ($this->createUsers) {
            [$created, $notEduID] = $this->createSlskeyUsers($switchNotInSlskey);
        }

        $this->writeDataToCsv($slskeyNotInSwitch, $switchNotInSlskey);
        $this->writeTxtSummary($this->switchGroupId, $this->slskeyCode, count($slskeyMembers), count($switchMembers), $slskeyNotInSwitch, $switchNotInSlskey, $created, $notEduID);
    }

    /**
     * Write data to csv
     *
     * @param array $slskeyMembers
     * @param array $switchMembers
     */
    public function writeDataToCsv($slskeyNotInSwitch, $switchNotInSlskey)
    {
        $this->writeCsv($this->timestampedDir . '/slskey_not_in_switch.csv', $slskeyNotInSwitch);
        $this->writeCsv($this->timestampedDir . '/switch_not_in_slskey.csv', $switchNotInSlskey);
    }

    /**
     * Helper function to write data to a CSV file
     *
     * @param string $fileName
     * @param Collection $data
     */
    private function writeCsv($fileName, $data)
    {
        $csvFilePath = storage_path('app/' . $fileName);

        // Remove the file if it exists
        if (file_exists($csvFilePath)) {
            unlink($csvFilePath);
        }

        $csv = fopen($csvFilePath, 'w');

        foreach ($data as $item) {
            fputcsv($csv, [$item]);
        }

        fclose($csv);
    }

    /**
     * Write summary to txt file
     *
     * @param string $switchGroupId
     * @param string $slskeyCode
     * @param int $countSlskeyMembers
     * @param int $countSwitchMembers
     * @param array $slskeyMembers
     * @param array $switchMembers
     */
    public function writeTxtSummary($switchGroupId, $slskeyCode, $countSlskeyMembers, $countSwitchMember, $slskeyNotInSwitch, $switchNotInSlskey, $created, $notCreated)
    {
        $txtFilePath = storage_path('app/' . $this->timestampedDir . '/summary.txt');

        // Remove the file if it exists
        if (file_exists($txtFilePath)) {
            unlink($txtFilePath);
        }

        $txt = fopen($txtFilePath, 'w');

        fwrite($txt, 'Switch Group ID: ' . $switchGroupId . PHP_EOL);
        fwrite($txt, 'Slskey Code: ' . $slskeyCode . PHP_EOL);

        fwrite($txt, 'Total Number of Slskey members: ' . $countSlskeyMembers . PHP_EOL);
        fwrite($txt, 'Number of Slskey members not in Switch: ' . count($slskeyNotInSwitch) . PHP_EOL);

        fwrite($txt, 'Total Number of Switch members: ' . $countSwitchMember . PHP_EOL);
        fwrite($txt, 'Number of Switch members not in Slskey: ' . count($switchNotInSlskey) . PHP_EOL);

        fwrite($txt, 'Number of Slskey members created: ' . count($created) . PHP_EOL);
        fwrite($txt, 'Number of Slskey members not created: ' . count($notCreated) . PHP_EOL);

        fclose($txt);
    }

    /**
     * Create Slskey Users
     *
     * @param array $switchNotInSlskey
     */
    public function createSlskeyUsers($switchNotInSlskey)
    {
        $activated = [];
        $notEduId = [];

        // For each switch User
        foreach ($switchNotInSlskey as $switchUser) {
            // Check if the user is an edu id
            if (!SlskeyUser::isPrimaryIdEduId($switchUser)) {
                $notEduId[] = $switchUser;

                continue;
            }

            // Check if user exists
            $slskeyUser = SlskeyUser::where('primary_id', $switchUser)->first();
            if ($slskeyUser) {
                // Do nothing ?
            } else {
                // Create a new Slskey User
                $slskeyUser = SlskeyUser::create([
                    'primary_id' => $switchUser,
                    'first_name' => 'Anon',
                    'last_name' => str(rand(10000, 99999)),
                ]);
            }

            $slskeyGroup = SlskeyGroup::where('slskey_code', $this->slskeyCode)->first();

            // Check if Activation exists
            $activation = SlskeyActivation::where('slskey_user_id', $slskeyUser->id)->where('slskey_group_id', $slskeyGroup->id)->first();
            if ($activation) {
                // Do nothing ?
            } else {
                // Add Activation
                $activation = SlskeyActivation::create([
                    'slskey_user_id' => $slskeyUser->id,
                    'slskey_group_id' => $slskeyGroup->id,
                    'activated' => true,
                    'activation_date' => now(),
                    'expiration_date' => null,
                    'deactivation_date' => null,
                    'blocked' => false,
                    'blocked_date' => null,
                    'remark' => null
                ]);

                // Add SlskeyHistory
                $slskeyHistory = SlskeyHistory::create([
                    'slskey_user_id' => $slskeyUser->id,
                    'slskey_group_id' => $slskeyGroup->id,
                    'action' => ActivationActionEnums::ACTIVATED,
                    'author' => null,
                    'trigger' => TriggerEnums::SYSTEM_MASS_IMPORT
                ]);

                $activated[] = $switchUser;
            }
        }

        return [$activated, $notEduId];
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use App\Models\SlskeyUser;
use App\Models\SlskeyGroup;
use App\Models\SlskeyReactivationToken;
use Carbon\Carbon;
use App\Models\SlskeyActivation;
use App\Enums\ActivationActionEnums;
use App\Enums\TriggerEnums;
use App\Models\SlskeyHistory;

class MigrateTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the mba reactivation tokens from old PURA into new SLSKey.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path  = base_path("database/data/tokens.xlsx");
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($path);

        $countMigrated = 0;
        $countNotFound = 0;
        $countTokenAlreadyUsed = 0;
        $countAlreadyActive = 0;
        $slskeyGroupMBA = SlskeyGroup::where('slskey_code', 'z01mba')->first();

        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() == 'Tabelle1') {
                $i = 0;
                foreach ($sheet->getRowIterator() as $rowNumber => $row) {
                    $cells = $row->getCells();
                    $i++;

                    $eduId = $cells[0];
                    $dateUsed = $cells[1];
                    $token = $cells[2];
                    $dateTokenCreated = Carbon::parse($cells[3]);
                    $hasAccess = $cells[4];
                    $dateExpiration = $cells[5];
                    $blockedCreated = $cells[6] == 'NULL' ? null : Carbon::parse($cells[6]);
                    $firstName = $cells[7];
                    $lastName = $cells[8];
                    $activationMail = $cells[9];

                    if ($eduId == '') {
                        continue;
                    }

                    echo $i . " - " . $eduId . " - ";

                    // Check if Token used => we don't need to migrate it
                    if ($dateUsed != 'NULL') {
                        echo("Token already used. " . "\r\n");
                        $countTokenAlreadyUsed++;

                        continue;
                    }

                    // Get SlskeyUser or create empty one
                    $slskeyUser = SlskeyUser::where('primary_id', $eduId)->first();
                    if (!$slskeyUser) {
                        echo("SlskeyUser not found. " . "\r\n");
                        $countNotFound++;
                        $slskeyUser = SlskeyUser::create([
                            'primary_id' => $eduId,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                        ]);
                    }

                    // Get SlskeyActivation or create empty one
                    $slskeyActivation = SlskeyActivation::where('slskey_user_id', $slskeyUser->id)
                        ->where('slskey_group_id', $slskeyGroupMBA->id)->first();

                    if ($slskeyActivation) {
                        // Users that are still active.
                        echo("SlskeyActivation already exists. " . "\r\n");
                        $countAlreadyActive++;
                    } else {
                        // Users that have been disabled already in old system.
                        echo("Creating new SlskeyActivation. " . "\r\n");
                        $slskeyActivation = SlskeyActivation::create([
                            'slskey_user_id' => $slskeyUser->id,
                            'slskey_group_id' => $slskeyGroupMBA->id,
                            'remark' => null,
                            'activated' => false,
                            'activation_date' => null,
                            'deactivation_date' => $blockedCreated,
                            'expiration_date' => null,
                            'expiration_disabled' => false,
                            'blocked' => false,
                            'blocked_date' => null,
                            'reminded' => false,
                            'webhook_activation_mail' => $activationMail,
                            'member_educational_institution' => false
                        ]);
                    }
                    // add one month and 14 days to dateTokenCreated, but leave dateTokenCreated unchanged
                    $tokenExpirationDate = (clone $dateTokenCreated)->addDays(60 + 14);

                    // Create the token
                    SlskeyReactivationToken::create([
                        'slskey_user_id' => $slskeyUser->id,
                        'slskey_group_id' => $slskeyGroupMBA->id,
                        'token' => $token,
                        'created_at' => $dateTokenCreated,
                        'expiration_date' => $tokenExpirationDate,
                        'used' => false,
                        'used_date' => null,
                    ]);

                    // Create a history that user was reminded
                    $slskeyHistory = SlskeyHistory::create([
                        'slskey_user_id' => $slskeyUser->id,
                        'slskey_group_id' => $slskeyGroupMBA->id,
                        'action' => ActivationActionEnums::TOKEN_SENT,
                        'author' => null,
                        'trigger' => TriggerEnums::SYSTEM_TOKEN_EXPIRATION,
                        'created_at' => $dateTokenCreated,
                    ]);

                    echo("Token created. Created at: " . $dateTokenCreated . ". Expiration Date: " . $tokenExpirationDate . "\r\n");
                    $countMigrated++;
                }
            }
        }
        echo("Updated " . $countMigrated . " rows from a total of " . $i . " rows.\r\n");
        echo("Tokens already used: " . $countTokenAlreadyUsed . "\r\n");
        echo("Users already active: " . $countAlreadyActive . "\r\n");
        echo("Users not found: " . $countNotFound . "\r\n");

        return Command::SUCCESS;
    }
}

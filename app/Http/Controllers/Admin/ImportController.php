<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

ini_set('max_execution_time', 180); // 3 minutes

use App\Enums\TriggerEnums;
use App\Events\DataImportProgressEvent;
use App\Helpers\WebhookMailActivation\WebhookMailActivationHelper;
use App\Interfaces\AlmaAPIInterface;
use App\Models\SlskeyGroup;
use App\Models\SlskeyUser;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ImportController extends Controller
{
    protected $userService;

    protected $almaApiService;

    protected $isCancelled = false;

    /**
     * ImportController constructor.
     *
     * @param UserService $userService
     * @param AlmaAPIInterface $almaApiService
     */
    public function __construct(UserService $userService, AlmaAPIInterface $almaApiService)
    {
        $this->userService = $userService;
        $this->almaApiService = $almaApiService;
    }

    /**
     * Index route for Admin Import
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('AdminImport/AdminImportIndex', []);
    }

    /**
     * Preview route for Admin Import
     *
     * @return InertiaResponse | RedirectResponse
     */
    public function preview(): InertiaResponse | RedirectResponse
    {
        $file = Request::file('csv_file');
        $path = $file->storeAs('csv', 'uploaded_file.csv', 'public');
        $importRows = $this->readCSVFile(storage_path('app/public/'.$path));

        // Process the CSV file as needed
        try {
        } catch (\Exception $e) {
            return Redirect::back()
                ->with('error', "Error while reading CSV file: {$e->getMessage()}");
        }

        return Inertia::render('AdminImport/AdminImportRun', [
            'givenRows' => $importRows,
        ]);
    }

    /**
     * Store route for Admin Import
     *
     * @return Response
     */
    public function store(): Response
    {
        ignore_user_abort(false);

        $importRows = Request::input('importRows');
        $checkIsActive = Request::input('checkIsActive');
        $testRun = Request::input('testRun');

        $currentRow = 0;

        foreach ($importRows as $row) {
            // Check if the cancellation flag is set
            if (Cache::get('is_import_cancelled')) {
                // If cancelled, break out of the loop
                break;
            }

            $result = $this->processImportRow($row, $testRun, $checkIsActive);

            $success = $result['success'];
            $message = $result['message'];
            $isActive = $result['isActive'] ?? null;
            $isVerified =$result['isVerified'] ?? null;

            // Broadcast progress to the frontend
            event(new DataImportProgressEvent($currentRow, $row['primary_id'], $row['slskey_code'], $success, $message, $isActive, $isVerified));

            $currentRow++;
        }

        // Set the cancellation flag to false
        Cache::put('is_import_cancelled', false, 60);

        // Redirect back
        return Response(200);
        // return Redirect::back()->with('success', 'Import completed successfully.');
    }

    /**
     * Process the import row
     *
     * @param array $row
     * @param boolean $testRun
     * @param boolean $checkIsActive
     * @return array
     */
    private function processImportRow(array $row, bool $testRun, bool $checkIsActive): array
    {
        // Get slskey group
        $slskeyGroup = SlskeyGroup::where('slskey_code', $row['slskey_code'])->first();

        if (!$slskeyGroup) {
            return ['success' => false, 'message' => 'SlskeyGroup not found', 'isActive' => false];
        }

        $isActive = null;
        if ($checkIsActive) {
            try {
                $response = $this->userService->verifySwitchStatusSlskeyUser($row['primary_id'], $slskeyGroup->slskey_code);
                $isActive = $response->success;
                if (! $isActive) {
                    return ['success' => false, 'message' => $response->message, 'isActive' => false];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage(), 'isActive' => false];
            }
        }

        // Check Alma user
        $token = config("services.alma.api_keys.{$slskeyGroup->alma_iz}");
        if (!$token) {
            return [
                'success' => false,
                'message' => 'No API token configured for this IZ.'
            ];
        }
        $this->almaApiService->setApiKey($token);

        $almaServiceResponse = $this->almaApiService->getUserByIdentifier($row['primary_id']);
        if (! $almaServiceResponse->success) {
            return ['success' => false, 'message' => $almaServiceResponse->errorText, 'isActive' => $isActive];
        }
        $almaUser = $almaServiceResponse->almaUser;

        // Check if we want to set Activation or Expiration Date
        try {
            $activationDate = $row['activation_date'] && $row['activation_date'] != 'NULL' ? Carbon::parse($row['activation_date']) : null;
            $expirationDate = $row['expiration_date'] && $row['expiration_date'] != 'NULL' ? Carbon::parse($row['expiration_date']) : null;
        } catch (\Exception $e) {
            return ['success' => false, 'message' => "Invalid date format: {$e->getMessage()}", 'isActive' => $isActive];
        }

        // Check for custom verification
        $userIsVerified = $slskeyGroup->checkCustomVerificationForUser($almaUser);
        if (!$userIsVerified) {
            return [
                'success' => false,
                'message' => 'User is not verified for this SlskeyGroup',
                'isActive' => $isActive,
                'isVerified' => $userIsVerified,
            ];
        }

        // Separate Z01 into Z01 and MBA
        $almaUserWebhookActivationMail = null;
        if ($slskeyGroup->slskey_code == 'Z01') {
            $mbaSlskeyGroup = SlskeyGroup::where('slskey_code', 'testmba')->first();
            $webhookMailActivationHelper = new WebhookMailActivationHelper($mbaSlskeyGroup->webhook_mail_activation_domains);
            $almaUserWebhookActivationMail = $webhookMailActivationHelper->getWebhookActivationMail($almaUser);
            if ($almaUserWebhookActivationMail) {
                $slskeyGroup = $mbaSlskeyGroup;
            }
        }

        // Check if test run, if so, return
        if ($testRun) {
            return [
                'success' => true,
                'message' => "User is ready for: {$slskeyGroup->slskey_code}",
                'isActive' => $isActive,
                'isVerified' => $userIsVerified,
            ];
        }

        // Activate user via SWITCH API
        $response = $this->userService->activateSlskeyUser(
            $row['primary_id'],
            $slskeyGroup->slskey_code,
            null, // author
            TriggerEnums::SYSTEM_MASS_IMPORT,
            null, // $almaUser,
            $almaUserWebhookActivationMail
        );

        // Error handling
        if (! $response->success) {
            return [
                'success' => false,
                'message' => $response->message,
                'isActive' => $isActive,
                'isVerified' => $userIsVerified,
            ];
        }

        // Set remark
        if ($row['remark'] && $row['remark'] != 'NULL') {
            $this->userService->setActivationRemark($row['primary_id'], $slskeyGroup->slskey_code, $row['remark']);
        }

        // Update User Details
        if ($almaUser) {
            $slskeyUser = SlskeyUser::where('primary_id', $row['primary_id'])->first();
            $slskeyUser->updateUserDetails($almaUser);
        }

        // Set Historic: Activation Date and Expiration Date
        if ($activationDate) {
            $this->userService->updateActivationDate($row['primary_id'], $slskeyGroup->slskey_code, $activationDate);
        }
        if ($expirationDate) {
            $this->userService->updateExpirationDate($row['primary_id'], $slskeyGroup->slskey_code, $expirationDate);
        }

        return [
            'success' => true,
            'message' => $response->message,
            'isActive' => $isActive,
            'isVerified' => $userIsVerified,
        ];
    }

    /**
     * Cancel the import process
     *
     * @return Response
     */
    public function cancelImport()
    {
        $this->isCancelled = true;
        Cache::put('is_import_cancelled', true, 60);

        return Response('CANCELLED');
    }

    /**
     * Read CSV file
     *
     * @param string $path
     * @return array
     */
    private function readCSVFile(string $path): array
    {
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $rows[] = [
                    'slskey_code' => $data[0],
                    'primary_id' => $data[1],
                    'activation_date' =>  count($data) > 2 ? $data[2] : null,
                    'expiration_date' => count($data) > 3 ? $data[3] : null,
                    'remark' => count($data) > 4 ? $data[4] : null,
                ];
            }
            fclose($handle);
        }

        return $rows;
    }
}

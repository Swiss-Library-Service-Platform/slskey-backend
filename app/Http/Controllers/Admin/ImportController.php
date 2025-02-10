<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\AlmaAPIInterface;
use App\Jobs\ImportCsvJob;
use App\Services\ActivationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ImportController extends Controller
{
    protected $activationService;

    protected $almaApiService;

    /**
     * ImportController constructor.
     *
     * @param ActivationService $activationService
     * @param AlmaAPIInterface $almaApiService
     */
    public function __construct(ActivationService $activationService, AlmaAPIInterface $almaApiService)
    {
        $this->activationService = $activationService;
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

        if (!$file) {
            \Log::error('No file was uploaded.');
            return Redirect::back()
                ->with('error', "No file was uploaded.");
        }

        $path = $file->storeAs('csv', 'uploaded_file.csv', 'public');
        $fullPath = storage_path('app/public/csv/uploaded_file.csv');

        // Log the path to ensure it is correct
        \Log::info('CSV file path: ' . $fullPath);

        if (!is_file($fullPath)) {
            \Log::error('The path is not a valid file: ' . $fullPath);
            return Redirect::back()
                ->with('error', "The uploaded file could not be found.");
        }

        try {
            $importRows = $this->readCSVFile($fullPath);
        } catch (\Exception $e) {
            \Log::error('Error while reading CSV file: ' . $e->getMessage());
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
    public function store(Request $request): Response
    {
        $importRows = Request::input('importRows');
        $testRun = Request::input('testRun');
        $withoutExternalApis = Request::input('withoutExternalApis');
        $checkIsActive = Request::input('checkIsActive');
        $setHistoryActivationDate = Request::input('setHistoryActivationDate');
        Cache::put('is_import_cancelled', false, 60);
        ImportCsvJob::dispatch($importRows, $testRun, $withoutExternalApis, $checkIsActive, $setHistoryActivationDate)->onConnection('redis_import_job');

        return Response(200);
    }

    /**
     * Cancel the import process
     *
     * @return Response
     */
    public function cancelImport()
    {
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
                    'is_member_education_institution' => count($data) > 5 ? $data[5] : null,
                    'firstname' => count($data) > 6 ? $data[6] : null,
                    'lastname' => count($data) > 7 ? $data[7] : null,
                ];
            }
            fclose($handle);
        }

        return $rows;
    }
}

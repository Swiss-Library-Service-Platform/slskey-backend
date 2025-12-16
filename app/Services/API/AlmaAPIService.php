<?php

namespace App\Services\API;

use App\DTO\AlmaServiceMultiResponse;
use App\DTO\AlmaServiceSingleResponse;
use App\Interfaces\AlmaAPIInterface;
use App\Models\AlmaUser;
use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;

/**
 * Class AlmaAPIService
 * Provides an interface for interacting with the Alma API.
 */
class AlmaAPIService implements AlmaAPIInterface
{
    protected $apiKey;

    protected $izCode;

    protected $baseUrl;

    protected $STAFF_RECORD_TYPES = ['STAFF'];

    /**
     * AlmaAPIService constructor.
     *
     * @param  string  $baseUrl  Base URL for the Alma API.
     * @param  string  $apiKey  API key for authentication.
     */
    public function __construct(string $baseUrl, string $izCode, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->izCode = $izCode;
        $this->apiKey = $apiKey;
    }

    /**
     * Get the API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Set the API key.
     *
     * @param string $izCode
     * @param string $apiKey
     * @return void
     */
    public function setApiKey(string $izCode, string $apiKey): void
    {
        $this->izCode = $izCode;
        $this->apiKey = $apiKey;
    }

    /**
     * Get a user from a single IZ, filter out staff user
     *
     * @param string $identifier
     * @param string $izCode
     * @return AlmaServiceSingleResponse
     */
    public function getUserFromSingleIz(string $identifier, string $izCode): AlmaServiceSingleResponse
    {
        $result = $this->fetchUserByIdentifierAndIzCode($identifier, $izCode);
        if ($result['success']) {
            return new AlmaServiceSingleResponse(true, $result['data'], null);
        } else {
            return new AlmaServiceSingleResponse(false, null, $result['message']);
        }
    }

    /**
     * Get a staff user from a single IZ
     *
     * @param string $identifier
     * @param string $izCode
     * @return AlmaServiceSingleResponse
     */
    public function getStaffUserFromSingleIz(string $identifier, string $izCode): AlmaServiceSingleResponse
    {
        $result = $this->fetchUserByIdentifierAndIzCode($identifier, $izCode, true);
        if ($result['success']) {
            return new AlmaServiceSingleResponse(true, $result['data'], null);
        } else {
            return new AlmaServiceSingleResponse(false, null, $result['message']);
        }
    }

    /**
     * Get a user from multiple IZs
     *
     * @param string $identifier
     * @param array $izCodes
     * @return AlmaServiceMultiResponse
    */
    public function getUserFromMultipleIzs(string $identifier, array $izCodes): AlmaServiceMultiResponse
    {
        $almaUsers = [];
        foreach ($izCodes as $izCode) {
            $result = $this->fetchUserByIdentifierAndIzCode($identifier, $izCode);
            if (!$result['success']) {
                // Continue trying other IZ codes (errors already logged)
                continue;
            }
            $almaUsers[] = $result['data'];
        }

        if (empty($almaUsers)) {
            // Return user-friendly message (detailed errors already logged)
            return new AlmaServiceMultiResponse(false, null, 'Unable to retrieve user information. Please try again or contact support.');
        }

        return new AlmaServiceMultiResponse(true, $almaUsers, null);
    }

    /**
     * Fetches a user by identifier for a given IZ code.
     *
     * @param string $identifier
     * @param string $izCode
     * @return array
     */
    private function fetchUserByIdentifierAndIzCode(string $identifier, string $izCode, bool $isStaffUser = false): array
    {
        $token = config("services.alma.api_keys.{$izCode}");
        if (!$token) {
            \Log::error('Alma API: Missing API Key', [
                'iz_code' => $izCode,
            ]);
            return ['success' => false, 'message' => 'System configuration error. Please contact support.'];
        }

        try {
            $this->setApiKey($izCode, $token);
            $almaUser = $this->getUserByIdentifier($identifier, $isStaffUser);

            return ['success' => true, 'data' => $almaUser];
        } catch (\Exception $e) {
            // Log detailed error for debugging (never exposed to user)
            \Log::warning('Alma API: User lookup failed', [
                'identifier' => $identifier,
                'iz_code' => $izCode,
                'is_staff_user' => $isStaffUser,
                'exception' => $e->getMessage(),
            ]);
            // Return user-friendly error message (safe to display)
            return ['success' => false, 'message' => 'Unable to find user. Please check the identifier and try again.'];
        }
    }

    /**
     * Query users with the given unique identifier via the API.
     *
     * @param  string  $identifier  Unique identifier (uniqueID, matriculation number, etc.).
     * @return AlmaUser|null User object or null if no user was found.
     * @throws \Exception If the user is not found or multiple users are found.
     */
    private function getUserByIdentifier(string $identifier, bool $isStaffUser): AlmaUser
    {
        if (empty($identifier)) {
            return null;
        }

        if ($isStaffUser) {
            $foundUser = $this->findStaffUser($identifier);
            if (!$foundUser) {
                throw new \Exception('User not found.');
            }
            if ($foundUser && !in_array($foundUser->record_type->value, $this->STAFF_RECORD_TYPES)) {
                throw new \Exception('Invalid user type.');
            }
        } else {
            $foundUsers = $this->findUsersQueryParallel($identifier);
            if (!$foundUsers) {
                throw new \Exception('User not found.');
            }
            if (count($foundUsers) > 1) {
                throw new \Exception('Multiple users found.');
            }
            $foundUser = $foundUsers[0];
        }

        $almaUser = AlmaUser::fromApiResponse($foundUser);
        $almaUser->alma_iz = $this->izCode;

        return $almaUser;
    }

    /**
     * Find Staff User Details in Alma API
     *
     * @param string $identifier
     * @return object
     */
    private function findStaffUser(string $identifier): object
    {
        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $response = $client->get("$this->baseUrl/users/$identifier", [
            'query' => [
                'apikey' => $this->apiKey,
            ],
        ]);

        $response = $this->decodeResponse($response->getBody()->getContents());

        return $response;
    }

    /**
     * Find Users in Alma API in parallel
     * - find users with query identifier
     * - find users with query primary_id
     *
     * @param [type] $identifier
     * @return array
     */
    private function findUsersQueryParallel($identifier)
    {
        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        // Define the two API endpoints
        $endpoint = $this->baseUrl . '/users';
        $queryPrimaryId = ['q' => "primary_id~$identifier", 'expand' => 'full', 'apikey' => $this->apiKey];
        $queryIdentifiers = ['q' => "identifiers~$identifier", 'expand' => 'full', 'apikey' => $this->apiKey];
        $queryEmail = ['q' => "email~$identifier", 'expand' => 'full', 'apikey' => $this->apiKey];

        // Prepare the requests with query parameters
        $promises = [
            'primary_id' => $client->getAsync($endpoint, ['query' => $queryPrimaryId]),
            'identifiers' => $client->getAsync($endpoint, ['query' => $queryIdentifiers]),
            'email' => $client->getAsync($endpoint, ['query' => $queryEmail]),
        ];

        // Wait for both requests to complete
        $responses = Utils::unwrap($promises);

        // Process the responses and return the found users
        $foundUsers = [];
        foreach ($responses as $key => $response) {
            $body = $response->getBody()->getContents();
            $responseBody = $this->decodeResponse($body);
            if ($responseBody->total_record_count > 0) {
                foreach ($responseBody->user as $user) {
                    $userExists = false;
                    // Filter out certain record types (e.g. Staff), see config
                    if (in_array($user->record_type->value, $this->STAFF_RECORD_TYPES)) {
                        continue;
                    }
                    // Check if the user is already in the list
                    foreach ($foundUsers as $foundUser) {
                        if ($user->primary_id == $foundUser->primary_id) {
                            $userExists = true;

                            break;
                        }
                    }
                    if (!$userExists) {
                        $foundUsers[] = $user;
                    }
                }
            }
        }

        return $foundUsers;
    }

    /**
     * Decodes given response depending on the current format mode of the API.
     *
     * @param  string  $response  API response.
     * @return mixed
     */
    private function decodeResponse(string $response)
    {
        if (!preg_match('/<error>/i', $response)) {
            return json_decode($response);
        } else {
            $doc = new DOMDocument('1.0');
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;

            if (!empty($response)) {
                $doc->loadXML($response);
            }

            return $doc;
        }
    }
}

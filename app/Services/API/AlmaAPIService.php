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
        $error = '';
        foreach ($izCodes as $izCode) {
            $result = $this->fetchUserByIdentifierAndIzCode($identifier, $izCode);
            if (!$result['success']) {
                $error = $error . "$izCode: {$result['message']} ";
                continue;
            }
            $almaUsers[] = $result['data'];
        }

        if (empty($almaUsers)) {
            return new AlmaServiceMultiResponse(false, null, $error);
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
    private function fetchUserByIdentifierAndIzCode(string $identifier, string $izCode, bool $allowStaffUser = false): array
    {
        $token = config("services.alma.api_keys.{$izCode}");
        if (!$token) {
            return ['success' => false, 'message' => "{$izCode}: Missing API Key in configuration."];
        }

        try {
            $this->setApiKey($izCode, $token);
            $almaUser = $this->getUserByIdentifier($identifier, $allowStaffUser);

            return ['success' => true, 'data' => $almaUser];
        } catch (\Exception $e) {
            // return ['success' => false, 'message' => "{$izCode}: {$e->getMessage()}"];
            return ['success' => false, 'message' => "{$e->getMessage()}"];
        }
    }

    /**
     * Query users with the given unique identifier via the API.
     *
     * @param  string  $identifier  Unique identifier (uniqueID, matriculation number, etc.).
     * @return AlmaUser|null User object or null if no user was found.
     * @throws \Exception If the user is not found or multiple users are found.
     */
    private function getUserByIdentifier(string $identifier, bool $allowStaffUser): AlmaUser
    {
        if (empty($identifier)) {
            return null;
        }

        $foundUsers = $this->findUsersParallel($identifier, $allowStaffUser);
        if (!$foundUsers) {
            throw new \Exception("User $identifier not found in $this->izCode");
        }
        if (count($foundUsers) > 1) {
            throw new \Exception('Multiple users found. Please provide a more specific identifier.');
        }
        $almaUser = AlmaUser::fromApiResponse($foundUsers[0]);
        $almaUser->alma_iz = $this->izCode;

        return $almaUser;
    }

    /**
     * Find Users in Alma API in parallel
     * - find users with query identifier
     * - find users with query primary_id
     *
     * @param [type] $identifier
     * @param boolean $allowStaffUser
     * @return array
     */
    private function findUsersParallel($identifier, bool $allowStaffUser)
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
                    if ($allowStaffUser && in_array($user->record_type->value, $this->STAFF_RECORD_TYPES)) {
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
     * Makes an HTTP request to the API with the given parameters.
     *
     * @param  string  $action  API action.
     * @param  string  $actionType  HTTP request type (GET by default).
     * @param  string[]  $queryParams  Query parameters.
     * @param  string  $bodyData  Request body data.
     * @return array [HTTP status code, API response].
    */
    /*
    private function makeRequest(
       string $action,
       string $actionType = 'GET',
       array $queryParams = [],
       string $bodyData = ''
    ): array {
       // Add the API key to the query parameters
       $queryParams['apikey'] = $this->apiKey;

       $url = $this->baseUrl . $action;

       $client = new Client([
           'headers' => [
               'Accept' => 'application/json',
               'Content-Type' => 'application/json',
           ],
       ]);

       $options = [
           'query' => $queryParams,
       ];

       if (!empty($bodyData)) {
           $options['body'] = $bodyData;
       }

       try {
           $response = $client->request($actionType, $url, $options);

           $statusCode = $response->getStatusCode();
           $body = $response->getBody()->getContents();

           $responseBody = $this->decodeResponse($body);

           return [$statusCode, $responseBody];
       } catch (RequestException $e) {
           // If the request has an exception, get the response from it
           if ($e->hasResponse()) {
               $response = $e->getResponse();
               $statusCode = $response->getStatusCode();
               $body = $response->getBody()->getContents();

               $errorData = $this->decodeResponse($body);

               return [$statusCode, $errorData];
           }

           // If there is no response, rethrow the exception
           throw $e;
       }
    }
   */
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

<?php
/**
 * Client for SWITCH Edu-ID Shared Attributes API
 *
 * PHP version 5
 * Copyright (C) project swissbib, University Library Basel, Switzerland
 * http://www.swissbib.org  / http://www.swissbib.ch / http://www.ub.unibas.ch
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category Swissbib
 *
 * @author   Lionel Walter <lionel.walter@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     http://www.swissbib.ch
 */

namespace App\Services\API;

use App\Interfaces\SwitchAPIInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class SwitchSharedAttributesAPIClient
 *
 * @category Swissbib
 *
 * @author   Lionel Walter <lionel.walter@unibas.ch>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link     http://www.swissbib.ch
 */
class SwitchAPIService implements SwitchAPIInterface
{
    /**
     * Switch Api configuration.
     *
     * @var array containing host, National Licence group id, ...
     */
    protected $configSwitchApi;

    /**
     * SwitchApi constructor.
     *
     * @param  array  $config  Configuration (username, password,
     *                         national_licence_programme_group_id,
     *                         base_endpoint_url)
     *
     * @throws \Exception
     */
    public function __construct(mixed $base_url, mixed $api_user, mixed $api_password, mixed $natlic_group)
    {
        if (isset($api_user)) {
            $this->configSwitchApi['auth_user'] = $api_user;
        } else {
            throw new \Exception(
                'Was not possible to find the SWITCH API '.
                'auth_user.'
            );
        }

        if (isset($api_password)) {
            $this->configSwitchApi['auth_password'] = $api_password;
        } else {
            throw new \Exception(
                'Was not possible to find the SWITCH API '.
                'auth_password.'
            );
        }

        if (isset($base_url)) {
            $this->configSwitchApi['base_endpoint_url']
                = $base_url;
        } else {
            throw new \Exception(
                'Was not possible to find the SWITCH API '.
                'base_endpoint_url.'
            );
        }

        //national_licence_programme_group_id is not mandatory
        if (isset($natlic_group)) {
            $this->configSwitchApi['national_licence_programme_group_id']
                = $natlic_group;
        }

        $this->configSwitchApi['schema_patch']
            = 'urn:ietf:params:scim:api:messages:2.0:PatchOp';
        $this->configSwitchApi['operation_add']
            = 'add';
        $this->configSwitchApi['operation_remove']
            = 'remove';
        $this->configSwitchApi['path_member']
            = 'members';
    }

    /**
     * Set national-licence-compliant flag to the user.
     *
     * @param  string  $userExternalId  External id
     * @return void
     *
     * @throws \Exception
     */
    public function setNationalCompliantFlag(string $userExternalId)
    {
        // 1 create a user
        $internalId = $this->createSwitchUser($userExternalId);
        // $internalId = $this->getSwitchUserInfoFromExternalId($userExternalId)->id;

        // 2 Add user to the National Compliant group
        $this->addUserToNationalCompliantGroup($internalId);
        // 3 verify that the user is on the National Compliant group
        if (! $this->userIsOnNationalCompliantSwitchGroup($userExternalId)) {
            throw new \Exception(
                'Was not possible to add user to the '.
                'national-licence-compliant group'
            );
        }
    }

    /**
     * Add a Switch edu-ID user to a group, for example to set
     * common-lib-terms for a given publisher
     *
     * @param  string  $userExternalId  EduId number like 169330697816@eduid.ch
     * @param  string  $publisherId  The id of the group in Switch API
     * @return void
     *
     * @throws \Exception
     */
    public function activatePublisherForUser(string $userExternalId, string $publisherId)
    {
        // 1 create a user
        $internalId = $this->createSwitchUser($userExternalId);
        // $internalId = $this->getSwitchUserInfoFromExternalId($userExternalId)->id;
        // 2 Add user to the group
        $this->addUserToGroup($internalId, $publisherId);
        // 3 verify that the user is on the National Compliant group
        if (! $this->userIsOnGroup($userExternalId, $publisherId)) {
            throw new \Exception(
                'Was not possible to add user to the '.
                'publisher group '.$publisherId
            );
        }
    }

    /**
     * Create a user in the National Licenses registration platform.
     *
     * @param  string  $externalId  External id
     * @return mixed
     *
     * @throws \Exception
     */
    protected function createSwitchUser(string $externalId)
    {
        $requestBody = ['externalID' => $externalId];
        [$statusCode, $responseBody] = $this->makeRequest('POST', 'Users', $requestBody);
        if ($statusCode != 200) {
            throw new \Exception("Status code: $statusCode");
        }

        return $responseBody->id;
    }

    /**
     * Get an instance of the HTTP Client with some basic configuration.
     *
     * @param  string  $method  Method
     * @param  string  $relPath  Relative path
     * @param  string  $basePath  The base path
     * @return \GuzzleHttp\Client
     *
     * @throws \Exception
     */
    protected function getBaseClient()
    {
        $username = $this->configSwitchApi['auth_user'];
        $password = $this->configSwitchApi['auth_password'];

        if (empty($username) || empty($password)) {
            throw new \Exception('SWITCH API credentials not found.');
        }

        $client = new Client([
            'base_uri' => $this->configSwitchApi['base_endpoint_url'],
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'auth' => [$username, $password],
        ]);

        return $client;
    }

    protected function makeRequest(
        string $method,
        string $relPath,
        ?array $body = null
    ) {
        $client = $this->getBaseClient();

        try {
            if ($body) {
                $response = $client->request($method, $relPath, ['body' => json_encode($body, JSON_UNESCAPED_SLASHES)]);
            } else {
                $response = $client->request($method, $relPath);
            }

            $statusCode = $response->getStatusCode();
            $encodedBody = $response->getBody()->getContents();
            $responseBody = json_decode($encodedBody);

            return [$statusCode, $responseBody];
        } catch (RequestException $e) {
            // If the request has an exception, get the response from it
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();

                return [$statusCode, $responseBody];
            }

            // If there is no response, rethrow the exception
            throw $e;
        }
    }

    /**
     * Add user to the National Licenses Programme group on the National Licenses
     * registration platform.
     *
     * @param  string  $userInternalId  User internal id
     * @return void
     *
     * @throws \Exception
     */
    protected function addUserToNationalCompliantGroup(string $userInternalId)
    {
        $this->addUserToGroup(
            $userInternalId,
            $this->configSwitchApi['national_licence_programme_group_id']
        );
    }

    /**
     * Add user to the National Licenses Programme group on the National Licenses
     * registration platform.
     *
     * @param  string  $userInternalId  User internal id
     * @param  string  $groupId  Group id
     * @return void
     *
     * @throws \Exception
     */
    protected function addUserToGroup(string $userInternalId, string $groupId)
    {
        $requestBody = [
            'schemas' => [
                $this->configSwitchApi['schema_patch'],
            ],
            'Operations' => [
                [
                    'op' => $this->configSwitchApi['operation_add'],
                    'path' => $this->configSwitchApi['path_member'],
                    'value' => [
                        [
                            '$ref' => $this->configSwitchApi['base_endpoint_url'].
                                '/Users/'.
                                $userInternalId,
                            'value' => $userInternalId,
                        ],
                    ],
                ],
            ],
        ];

        [$statusCode, $responseBody] = $this->makeRequest('PATCH', 'Groups/'.$groupId, $requestBody);
        if ($statusCode != 200) {
            throw new \Exception("Status code: $statusCode");
        }
    }

    /**
     * Check if the user is on the National Licenses Programme group.
     *
     * @param  string  $userExternalId  User external id
     * @return bool
     *
     * @throws \Exception
     */
    public function userIsOnNationalCompliantSwitchGroup(string $userExternalId)
    {
        return $this->userIsOnGroup(
            $userExternalId,
            $this->configSwitchApi['national_licence_programme_group_id']
        );
    }

    /**
     * Check if the user is on the group $groupId
     *
     * @param  string  $userExternalId  User external id
     * @param  array  $groupIds  Group Ids
     * @return bool
     *
     * @throws \Exception
     */
    public function userIsOnAllGroups(string $userExternalId, array $groupIds)
    {
        //$internalId = $this->createSwitchUser($userExternalId);
        try {
            $internalId = $this->getSwitchUserInfoFromExternalId($userExternalId)->id;
        } catch (\Exception $e) {
            throw new \Exception("Switch Error: User " . $userExternalId . " not found");
        }

        try {
            $switchUser = $this->getSwitchUserInfo($internalId);
        } catch (\Exception $e) {
            throw new \Exception("Switch Error: Switch Info for user " . $userExternalId . " not found");
        }

        // check if switchUser has all groups that are passed in
        $switchUserGroups = array_map(function ($object) {
            return $object->value;
        }, $switchUser->groups);

        $diff = array_diff($groupIds, $switchUserGroups);

        return empty($diff);
    }

    /**
     * Check if the user is on the group $groupId
     *
     * @param  string  $userExternalId  User external id
     * @param  string  $groupId  Group Id
     * @return bool
     *
     * @throws \Exception
     */
    public function userIsOnGroup(string $userExternalId, string $groupId)
    {
        //$internalId = $this->createSwitchUser($userExternalId);
        $internalId = $this->getSwitchUserInfoFromExternalId($userExternalId)->id;

        $switchUser = $this->getSwitchUserInfo($internalId);
        foreach ($switchUser->groups as $group) {
            if ($group->value === $groupId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user info from the National Licenses registration platform.
     *
     * @param  string  $internalId  User external id
     * @return mixed
     *
     * @throws \Exception
     */
    protected function getSwitchUserInfo(string $internalId)
    {
        [$statusCode, $responseBody] = $this->makeRequest('GET', 'Users/'.$internalId);
        if ($statusCode != 200) {
            throw new \Exception("Status code: $statusCode");
        }

        return $responseBody;
    }

    /**
     * Unset the national compliant flag from the user.
     *
     * @param  string  $userExternalId  User external id
     * @return void
     *
     * @throws \Exception
     */
    public function unsetNationalCompliantFlag(string $userExternalId)
    {
        $this->removeUserFromGroupAndVerify(
            $userExternalId,
            $this->configSwitchApi['national_licence_programme_group_id']
        );
    }

    /**
     * Remove user from group.
     *
     * @param  string  $userExternalId  User external id
     * @param  string  $groupId  Group id
     * @return void
     *
     * @throws \Exception
     */
    public function removeUserFromGroupAndVerify(string $userExternalId, string $groupId)
    {
        // 1 create a user
        // $internalId = $this->createSwitchUser($userExternalId);
        $internalId = $this->getSwitchUserInfoFromExternalId($userExternalId)->id;

        // 2 Add user to the National Compliant group
        $this->removeUserFromGroup($internalId, $groupId);
        // 3 verify that the user is not any more in the group
        if ($this->userIsOnGroup($userExternalId, $groupId)) {
            throw new \Exception(
                'Was not possible to remove the user'.
                $userExternalId.'from the group'.
                $groupId
            );
        }
    }

    /**
     * Remove a national licence user from the national-licence-programme-group.
     *
     * @param  string  $userInternalId  User internal id
     * @return void
     *
     * @throws \Exception
     */
    protected function removeUserFromNationalCompliantGroup(string $userInternalId)
    {
        $this->removeUserFromGroup(
            $userInternalId,
            $this->configSwitchApi['national_licence_programme_group_id']
        );
    }

    /**
     * Remove a user from a publisher group.
     *
     * @param  string  $userInternalId  User internal id
     * @param  string  $groupId  Group Id
     * @return void
     *
     * @throws \Exception
     */
    protected function removeUserFromGroup(string $userInternalId, string $groupId)
    {
        $params = [
            'schemas' => [
                $this->configSwitchApi['schema_patch'],
            ],
            'Operations' => [
                [
                    'op' => $this->configSwitchApi['operation_remove'],
                    'path' => $this->configSwitchApi['path_member'].
                        "[value eq \"$userInternalId\"]",
                ],
            ],
        ];
        [$statusCode, $responseBody] = $this->makeRequest('PATCH', 'Groups/'.$groupId, $params);
        if ($statusCode != 200) {
            throw new \Exception("Status code: $statusCode");
        }
    }

    /**
     * Get the number of members in a group.
     *
     * @param  string  $groupId  Group id
     * @return int
     *
     * @throws \Exception
     */
    public function getMembersCountForGroupId(string $groupId)
    {
        [$statusCode, $responseBody] = $this->makeRequest('GET', 'Groups/'.$groupId);
        if ($statusCode != 200) {
            throw new \Exception("Status code: $groupId $statusCode");
        }

        return count($responseBody->members);
    }

    /**
     * Get user info from the National Licenses registration platform.
     *
     * @param  string  $internalId  User external id
     * @return mixed
     *
     * @throws \Exception
     */
    protected function getSwitchUserInfoFromExternalId(mixed $externalId)
    {
        [$statusCode, $responseBody] = $this->makeRequest('GET', 'Users?filter=externalID eq '.$externalId);
        if ($statusCode != 200) {
            throw new \Exception();
        }

        return $responseBody;
    }
}

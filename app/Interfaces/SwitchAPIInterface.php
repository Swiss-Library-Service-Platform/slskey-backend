<?php

namespace App\Interfaces;

interface SwitchAPIInterface
{
    public function getMembersCountForGroupId(string $groupId);

    public function setNationalCompliantFlag(string $userExternalId);

    public function activatePublisherForUser(string $userExternalId, string $publisherId);

    public function userIsOnNationalCompliantSwitchGroup(string $userExternalId);

    public function userIsOnGroup(string $userExternalId, string $groupId);

    public function userIsOnAllGroups(string $userExternalId, array $groupIds);

    public function unsetNationalCompliantFlag(string $userExternalId);

    public function removeUserFromGroupAndVerify(string $userExternalId, string $groupId);
}

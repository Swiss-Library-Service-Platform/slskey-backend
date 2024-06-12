<?php

use App\Interfaces\SwitchAPIInterface;
use Illuminate\Support\Facades\App;

function mockSwitchApiServiceActivation($currentMockObject = null)
{
    if ($currentMockObject) {
        $switchApiServiceMock = $currentMockObject;
    } else {
        $switchApiServiceMock = Mockery::mock(SwitchAPIInterface::class);
    }
    $switchApiServiceMock->shouldReceive('activatePublisherForUser')->andReturn(null);
    App::instance(SwitchAPIInterface::class, $switchApiServiceMock);

    return $switchApiServiceMock;
}

function mockSwitchApiServiceDeactivation($currentMockObject = null)
{
    if ($currentMockObject) {
        $switchApiServiceMock = $currentMockObject;
    } else {
        $switchApiServiceMock = Mockery::mock(SwitchAPIInterface::class);
    }
    $switchApiServiceMock->shouldReceive('removeUserFromGroupAndVerify')->andReturn(null);
    App::instance(SwitchAPIInterface::class, $switchApiServiceMock);

    return $switchApiServiceMock;
}

function mockSwitchApiServiceUserIsOnAllGroups($return = null)
{
    $switchApiServiceMock = Mockery::mock(SwitchAPIInterface::class);
    $switchApiServiceMock->shouldReceive('userIsOnAllGroups')->andReturn($return);
    App::instance(SwitchAPIInterface::class, $switchApiServiceMock);
}

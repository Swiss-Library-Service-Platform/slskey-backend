<?php

use App\Services\MailService;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\App;

function mockMailServiceTokenSend($return = null)
{
    $mailServiceMock = Mockery::mock(MailService::class);
    $mailServiceMock->shouldReceive('sendReactivationTokenUserMail')->andReturn(null);
    App::instance(MailService::class, $mailServiceMock);

    return $mailServiceMock;
}

function mockMailServiceRemindExpiringUsers($return = null)
{
    $sentMessageMock = Mockery::mock(SentMessage::class);

    $mailServiceMock = Mockery::mock(MailService::class);
    $mailServiceMock->shouldReceive('sendRemindExpiringUserMail')->andReturn($sentMessageMock);
    App::instance(MailService::class, $mailServiceMock);

    return $mailServiceMock;
}

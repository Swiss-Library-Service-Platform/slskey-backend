<?php

use App\Models\User;

beforeEach(function () {
    $this->seed('Database\Seeders\Test\TestSlskeyGroupSeeder');
});

test('export function downloads users export file', function () {
    // Mocking the Excel class and its download method
    /*
    $excelMock = TestSuite::swap(Excel::class, function (MockInterface $mock) {
        $mock->shouldReceive('download')->once();
    });
    */
    $user = User::factory()->edu_id()->withRandomPermissions(1)->create();
    $this->actingAs($user);
    // Calling the controller function
    $response = $this->get('/users/export');

    // Asserting that the response is successful
    $response->assertStatus(200);
    // Asserting that the response is a download
    $response->assertHeader('Content-Disposition', 'attachment; filename=slskey_users.xlsx');
    // Asserting that the response is an Excel file
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

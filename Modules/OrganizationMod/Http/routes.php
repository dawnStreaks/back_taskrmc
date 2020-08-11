<?php

Route::group(['middleware' => 'web', 'prefix' => 'organizationmod', 'namespace' => 'Modules\OrganizationMod\Http\Controllers'], function()
{
    Route::get('/', 'OrganizationModController@index');
});

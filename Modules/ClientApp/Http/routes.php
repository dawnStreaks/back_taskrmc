<?php

Route::group(['middleware' => 'api', 'prefix' => 'clientapp', 'namespace' => 'Modules\ClientApp\Http\Controllers'], function()
{
    Route::get('/', 'ClientAppController@index');
});

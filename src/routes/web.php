<?php

Route::group(['middleware' => ['web']], function () {
    Route::get('image/{realname}/{size?}', 'ImageController@show')->name('image.show');
});
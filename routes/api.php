<?php

use App\Http\Middleware\SetTenantSchema;
use App\Models\Tenants\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(SetTenantSchema::class)->group(function () {
    Route::get('/projects', function () {
        return Project::all();
    });
});

<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $connection = 'tenant';

    protected $fillable = ['name'];
}

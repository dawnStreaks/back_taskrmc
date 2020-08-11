<?php

namespace Modules\ClientApp\Entities;

use Illuminate\Database\Eloquent\Model AS Master_Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends Master_Model
{
    use SoftDeletes ;
    protected $fillable = [];
}

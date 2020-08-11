<?php

namespace Modules\ClientApp\Entities;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translations extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;


    protected $table = "trans_table" ;
    protected $primaryKey = "id" ;
    protected $fillable = ['tenant_id', 'key_type', 'key_pos' , 'key_name','value_ar','value_en','svalue_ar','svalue_en'];
    // ...

    protected $auditInclude = [
        'tenant_id', 'key_type', 'key_pos' , 'key_name','value_ar','value_en','svalue_ar','svalue_en','key_type', 'key_pos' , 'key_name','value_ar','value_en','svalue_ar','svalue_en'
    ];

    protected $auditEvents = [
        'created',
        'updated',
        'deleted',
        'restored',
    ];


}

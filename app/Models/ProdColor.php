<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdColor extends Model
{
    protected $fillable = ['id_product', 'color_name', 'color_hexa'];
    protected $guarded = [ 'created_at', 'update_at'];
    protected $table = 'prod_colors';
}

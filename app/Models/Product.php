<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class Product extends Model
{
    protected $fillable = [ 'name', 'price', 'description' , 'color_variation'];
    protected $table = 'products';

    public function getProductAll() {
        $sql =  (
            "SELECT     a.id as id_product, b.id as id_prodcolor, a.name, a.price, 
                        a.description, a.color_variation, b.color_hexa, b.color_name
               FROM     products AS a
          LEFT JOIN     prod_colors AS b ON a.id = b.id_product     
           ORDER BY     a.id DESC"
        );
 
        try{
           $results = DB::select($sql);

        } catch (\Exception $ex){
            $results = (object) ['error' => $ex->getMessage()];
        }

        return $results;
    }

    public function getProductId($id, $id_prodcolor = '') {

        $and_id_prodcolor = '';

        if ( !empty($id_prodcolor) )  {
            $and_id_prodcolor = " AND b.id = {$id_prodcolor} ";
        }

        $sql =  (
            "SELECT     a.id as id_product, b.id as id_prodcolor, a.name, a.price, 
                        a.description, a.color_variation, b.color_hexa, b.color_name
               FROM     products AS a
          LEFT JOIN     prod_colors AS b ON a.id = b.id_product      
              WHERE     a.id  =  $id  $and_id_prodcolor 
           ORDER BY     a.id DESC"
              
        );

        try{
            $results = DB::select($sql);

        } catch (\Exception $ex){
            $results = (object) ['error' => $ex->getMessage()];
        }
        return $results;
    }
    
}

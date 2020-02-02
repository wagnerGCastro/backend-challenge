<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    
    protected $fillable = [  'name', 'price', 'description' , 'color_variation'];
    protected $table = 'products';


    public function getProductAll() {
        $sql =  (
            "SELECT     a.id as id_products, b.id as id_prodcolors, a.name, a.price, 
                        a.description, a.color_variation, b.color_hexa, b.color_name
               FROM     products AS a
          LEFT JOIN     prod_colors AS b ON a.id = b.id_products       
           ORDER BY     a.id DESC"
        );
 
        try{
           $results = DB::select($sql);

        } catch (\Exception $ex){
            
            $results = (object) ['error' => $ex->getMessage()];
        }

        return $results;
    }

    public function getProductId($id) {
        $sql =  (
            "SELECT     a.id as id_products, b.id as id_prodcolors, a.name, a.price, 
                        a.description, a.color_variation, b.color_hexa, b.color_name
               FROM     products AS a
          LEFT JOIN     prod_colors AS b ON a.id = b.id_products       
              WHERE     a.id  =  $id
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

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProdColor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductFormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Validator;

class ProductController extends Controller
{
    /**
     * @var product
     */
    private $product;

    /**
     * @var prodColor
     */
    private $prodColor;

      /**
     * @var prodColor
     */
    private $productFormRequest;


    public function __construct(Product $product, ProdColor  $prodColor)
    {
        $this->product = $product;
        $this->prodColor = $prodColor;
        $this->productFormRequest = new ProductFormRequest;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = $this->product->getProductAll();

        // checks if there was a database error
        if ( $response = $this->responseDatabaseError($result) ) { return $response; }

        return response()->json( $result );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if( isset($data['id_prodcolor']) ) {
            unset($data['id_prodcolor']);
        }

        // If color_variation = 'Y'
        $color_variation = $request->color_variation;
        if( isset($color_variation) && $color_variation !== null && $color_variation == 'Y' ){
            // Rules
            $rules = [
                'color_name'      => 'min:2|max:60',
                'color_hexa'      => 'min:3|max:9'
            ];

            // Validator
            $validator = Validator::make($request->all() , $rules);

            if ($validator->fails()) {
                return response()->json(formatMessage(400, $validator->messages()), 400);
            }
        } else {

            // Chech if the fields exists and remove
            if( isset($data['color_name']) || isset($data['color_hexa']) ) {
                unset($data['color_name']);
                unset($data['color_hexa']);
            }
        }

        // Validation request
        if( $response = $this->productFormRequest->Validador($data) ) { return $response; };

        try {

            // Insert table product
            $requestData          = $request->all();
            $requestData['price'] = formatNumToMysql($request->price);
            $prodcuinsertedId     = $this->product->create($requestData);

            // Insert table prod_color
            $color_variation = $requestData['color_variation'];
            if( isset($color_variation) && $color_variation !== null && $color_variation == 'Y' &&  $prodcuinsertedId !== 0):
                $this->prodColor->id_product = $prodcuinsertedId->id;
                $this->prodColor->color_name = $requestData['color_name'];
                $this->prodColor->color_hexa = $requestData['color_hexa'];
                $this->prodColor->save();
            endif;

            return response()->json(formatMessage(201, 'Product successfully created!') , 201);

        } catch (Exception $e) {

            if(config('app.debug')) {
                return response()->json(formatMessage(500, $e->getMessage()),  500);
            }
            return response()->json(formatMessage(500, 'Something unexpected prevented him from fulfilling the request.'), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Checks if parameter was passed with $id of type Integer
        if( $response = $this->checkParamId($id) ) { return $response; };

        // Checks if the product exists
        if ( $response = $this->findDatabaseId($id) ){ return $response; }

        $result = $this->product->getProductId($id);

        // checks if there was a database error
        if ( $response = $this->responseDatabaseError($result) ) { return $response; }

        return response()->json($result[0]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        // Checks if parameter was passed with $id of type Integer
        if( $response = $this->checkParamId($id) ) { return $response; };

        // checks if the product exists
        $result = $this->findDatabaseId($id);

        // Checks if exists  product in database
        if( $response = $this->findDatabaseId($id) ) { return $response; };

        // Validation request
        if( $response = $this->productFormRequest->Validador($data) ) { return $response; };

        // Update table prod_colors if color_variation == 'Y' or Create
        $color_variation = $request->color_variation;
        if( isset($color_variation) && $color_variation !== null && $color_variation == 'Y' ):
            if(!empty($data['id_prodcolor'])):
                // Chech if the fields exists
                if( !isset($data['color_name']) || !isset($data['color_hexa']) ) {
                    return response()->json(formatMessage(400, 'color_name or color_hexa or fields are required'), 400);
                }

                $data['id_prodcolor'] = (int) filter_var($data['id_prodcolor'], FILTER_SANITIZE_NUMBER_INT);
                $data['id_product']   = (int) filter_var($id, FILTER_SANITIZE_NUMBER_INT);

                if( is_integer($data['id_prodcolor']) ):
                   if( $response = $this->upadteVariationColor($data) ) { return $response; };
                else:
                    return response()->json(formatMessage(400, 'check if the  id_prodolor was entered correctly, or does not exist.'), 400);
                endif;
            else:
                $this->storeVariationColor($request, $id);
            endif;
        endif;

        // Check if the fields exists and remove
        $data = array_except( $data, ['id_prodcolor','id_product','color_name', 'color_hexa']);

        // Update table products
        try {
            // Update
            $data['price'] = formatNumToMysql($data['price']);
            DB::table('products')->where('id', $id )->update($data);

            return response()->json(formatMessage(200, 'Successfully!') , 200);

        } catch (\Exception $e) {

            if(config('app.debug')) {
                return response()->json(formatMessage(1011, $e->getMessage()),  500);
            }
            return response()->json(formatMessage(500, 'Something unexpected prevented him from fulfilling the request.'), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Checks if parameter was passed with $id of type Integer
        if( $response = $this->checkParamId($id) ) { return $response; };

        // Checks if exists  product in database
        if( $response = $this->findDatabaseId($id) ) { return $response; };

        try {

            DB::table('products')->where('id', $id )->delete();
            return response()->json(formatMessage(200,  'Successfully'), 200);

        }catch (\Exception $e) {

            if(config('app.debug')):
                return response()->json(formatMessage(1012, $e->getMessage()), 500);
            endif;

            return response()->json(formatMessage(500, 'Something unexpected prevented him from fulfilling the request.'), 500 );
        }
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $id
     * @return \Illuminate\Http\Response
     */
    private function checkParamId($id)
    {
        $validator = Validator::make(['id'=> $id], ['id' => 'integer']);
        if ($validator->fails()) {
            return  response()->json(formatMessage(400, 'Check that you have correctly entered the ID parameter!'), 400);
        }
    }

    /**
    * Checks if the product exists
    *
    * @param  \App\Product  $id
    * @return \Illuminate\Http\Response
    */
    private function findDatabaseId($id)
    {
        try {

            $result  = DB::table('products')->where('id', $id )->get();

            if( count( $result ) == 0 ):
                return response()->json(formatMessage(404, 'No results found.'), 404);
             endif;

        }catch (\Exception $e) {

            if(config('app.debug')):
                return response()->json(formatMessage(1012, $e->getMessage()), 500);
            endif;

            return response()->json(formatMessage(500, 'Something unexpected prevented him from fulfilling the request.'), 500 );
        }
    }

     /**
     * storeVariationColor a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $id
     * @return \Illuminate\Http\Response
     */
    private function storeVariationColor($request, $id)
    {
        // Rules
        $rules = [
            'id_product'     => 'required|integer|',
            'color_name'      => 'required|min:2|max:60|',
            'color_hexa'      => 'required|min:3|max:9|',
        ];

        $data = [
            'id_product' => $id,
            'color_hexa' => $request->color_hexa,
            'color_name' => $request->color_name,
        ];

        // Validator
        $validator = Validator::make($data , $rules);

        if ($validator->fails()) {
            return response()->json(formatMessage(404, $validator->messages()), 404);
        }

        try {
            // Create
           $this->prodColor->create($data);

        } catch (\Exception $e) {

            if(config('app.debug')) {
                return response()->json(formatMessage(1010, $e->getMessage()),  500);
            }
            return response()->json(formatMessage(1010, 'There was an error while performing save operation.'), 500);
        }
    }

    /**
    * upadteVariationColor a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    private function upadteVariationColor($data)
    {
        // checks if the product exists
        $results  = DB::table('prod_colors')->where(['id' => $data['id_prodcolor'], 'id_product' => $data['id_product']])->get();
        if( count($results) == 0 ):
            return response()->json(formatMessage(400, "No product registration with color variation was found."), 400);
        endif;

        // Rules
        $rules = [
            'color_name'      => 'required|min:2|max:60|',
            'color_hexa'      => 'required|min:3|max:9|',
        ];

        $dataUpadte = [
            'color_name'    => $data['color_name'],
            'color_hexa'    => $data['color_hexa'],
        ];

        // Validator
        $validator = Validator::make($dataUpadte , $rules);

        if ($validator->fails()) {
            return response()->json(formatMessage(400, $validator->messages()), 400);
        }

        try {
            // Update table prod_colors
            DB::table('prod_colors')->where('id', $data['id_prodcolor'] )->update($dataUpadte);

        } catch (\Exception $e) {
            if(config('app.debug')):
                return response()->json(formatMessage(1011, $e->getMessage()),  500);
            endif;

            return response()->json(formatMessage(500, 'Something unexpected prevented him from fulfilling the request'), 500);
        }
    }

    /**
    * responseDatabaseError checks if there was a database error
    *
    * @param object $result
    * @return \Illuminate\Http\Response
    */
    private function responseDatabaseError($result) {

        if( isset($result->error) && count($result->error) >= 1 ){

            if(config('app.debug')):
                return response()->json(formatMessage(500, $result->error),  500);
            endif;
            return response()->json(formatMessage(500, 'Something unexpected prevented him from fulfilling the request.'), 500);
        }
    }

}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Validator;
use App\Models\Product;
use App\Models\ProdColor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;


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

    public function __construct(Product $product, ProdColor  $prodColor)
    {
        $this->product = $product;
        $this->prodColor = $prodColor;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json($this->product->paginate(5));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Messages Errors
        $messages = [ 
            'price.regex'        => 'The price format is invalid.' ,
            'color_variation.in' => 'The selected color variation is invalid. Only Y or N is allowed.' 
        ];

        // Rules 
        $rules = [
            'name'            => 'required|min:2|max:120|unique:products',
            'price'           => 'required|min:1|regex:/^[0-9]{1,9}(,[0-9]{3})*(\.[0-9]+)*$/',
            'description'     => 'required|min:2|max:255',
            'color_variation' => 'max:1|in:Y,N,',
            'color_name'      => 'min:2|max:60',
            'color_hexa'      => 'min:9|max:9'
        ];
    
        // Validator
        $validator = Validator::make($request->all() , $rules, $messages);

        if ($validator->fails()) {
            return response()->json(formatMessage(404, $validator->messages()), 404);
        }

        try {

            // Insert table product
            $requestData          = $request->all();
            $requestData['price'] = formatNumToMysql($request->price);
            $prodcuinsertedId =  $this->product->create($requestData);

            // Insert table prod_color
            $color_variation = $requestData['color_variation'];
            if( isset($color_variation) && $color_variation !== null && $color_variation == 'Y' &&  $prodcuinsertedId !== 0):
                $this->prodColor->id_products = $prodcuinsertedId->id;
                $this->prodColor->color_name = $requestData['color_name'];
                $this->prodColor->color_hexa = $requestData['color_hexa'];
                $this->prodColor->save();
            endif;

            return response()->json(formatMessage(201, 'Product successfully created!') , 201);

        } catch (\Exception $e) {

            if(config('app.debug')) {
                return response()->json(formatMessage(1010, $e->getMessage()),  500);
            }
            return response()->json(formatMessage(1010, 'There was an error while performing save operation.'), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Checks if parameter was passed with $id of type Integer
        if( $response = $this->checkParamId($id) ) { return $response; };
        
        // checks if the product exists
        $product = $this->product->find($id);
        
        if(! $product){
            return response()->json(formatMessage(404, 'Product not found!'), 404);
        }

        // Rules 
        $rules = [
            'id_products'     => 'required|integer|',
            'color_name'      => 'required|min:2|max:60|',
            'color_hexa'      => 'required|min:9|max:9|',
        ];

        // Validator
        $validator = Validator::make($request->all() , $rules);

        if ($validator->fails()) {
            return response()->json(formatMessage(404, $validator->messages()), 404);
        }
        return response()->json(['code' => 200, 'product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Checks if parameter was passed with $id of type Integer
        if( $response = $this->checkParamId($id) ) { return $response; };
        
        // Messages Errors
        $messages = ['price.regex' => 'The price format is invalid.'];

        // Rules 
        $rules = [
            'name' => 'required|min:2|max:120',
            'price' => 'required|min:1|regex:/^[0-9]{1,9}(,[0-9]{3})*(\.[0-9]+)*$/',
            'description' => 'required|min:2|max:255',
            'color_name'      => 'min:2|max:60|',
            'color_hexa'      => 'min:9|max:9|',
        ];

        // Validator
        $validator = Validator::make($request->all() , $rules, $messages);

        if ($validator->fails()) {
            return response()->json(formatMessage(404, $validator->messages()), 404);
        }

        // Update table prod_colors if color_variation == 'Y' or Create
        $color_variation = $request->color_variation;
        if( isset($color_variation) && $color_variation !== null && $color_variation == 'Y' ):
             // checks if the id prod_color exists
            if( isset($request->id_prodcolor) ):
                $id = (int) filter_var($request->id_prodcolor, FILTER_SANITIZE_NUMBER_INT);

                if( isset($id ) && !is_null($id) && is_integer($id) ):
                    // Update
                    $this->upadteVariationColor($request);
                else:
                    return response()->json(formatMessage(410, 'check if the prod_colors table id was entered correctly, or does not exist.'), 410);
                    die;
                endif;
            else:
                // Create
                $this->storeVariationColor($request, $id);
            endif;
        endif;

        // Update table products
        try {

            $product = $this->product->find($id);

            // checks if the product exists
            if( !$product):
                return response()->json(formatMessage(410, 'Product not found!'), 410);
            endif;

            // Update
            $requestData          = $request->all();
            $requestData['price'] = formatNumToMysql($request->price);
            $product->update($requestData);

            return response()->json(formatMessage(201, 'Product updated successfully!') , 201);

        } catch (\Exception $e) {

            if(config('app.debug')) {
                return response()->json(formatMessage(1011, $e->getMessage()),  500);
            }
            return response()->json(formatMessage(1011, 'There was an error when performing the update operation'), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Checks if parameter was passed with $id of type Integer
        if( $response = $this->checkParamId($id) ) { return $response; };
        
        try {

            // checks if the product exists
            $product = $this->product->find($id);

            if( !$product):
                return response()->json(formatMessage(410, 'Product not found or already deleted.'), 410);
            endif;

            $product->delete();
            return response()->json(formatMessage(200, 'Product: ' . $product->name . ' successfully removed!'), 200);

        }catch (\Exception $e) {

            if(config('app.debug')):
                return response()->json(formatMessage(1012, $e->getMessage()), 500);
            endif;

            return response()->json(formatMessage(1012, 'There was an error while performing the delete operation!'), 500 );
        }
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    private function checkParamId($id)
    {
        $validator = Validator::make(['id'=> $id], ['id' => 'integer']);
        if ($validator->fails()) {
            return  response()->json(formatMessage(404, 'Check if you entered the product ID correctly!'), 404);
        }
    }

     /**
     * storeVariationColor a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function storeVariationColor($request, $id)
    {
        // Rules 
        $rules = [
            'id_products'     => 'required|integer|',
            'color_name'      => 'required|min:2|max:60|',
            'color_hexa'      => 'required|min:9|max:9|',
        ];

        $data = [
            'id_products' => $id,
            'color_hexa' => $request->color_hexa,
            'color_name' => $request->color_name,
        ]; 
    
        // Validator
        $validator = Validator::make($data , $rules);

        if ($validator->fails()) {
            return response()->json(formatMessage(404, $validator->messages()), 404);
            die;
        }

        try {
            // Create
           $this->prodColor->create($data);

        } catch (\Exception $e) {

            if(config('app.debug')) {
                return response()->json(formatMessage(1010, $e->getMessage()),  500);
                die;
            }
            return response()->json(formatMessage(1010, 'There was an error while performing save operation.'), 500);
            die;
        }
    }

    /**
    * upadteVariationColor a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    private function upadteVariationColor($request)
    {
        
        // Rules 
        $rules = [
            'color_name'      => 'min:2|max:60|',
            'color_hexa'      => 'min:9|max:9|',
        ];

        $data = [
            'color_name' => $request->color_name,
            'color_hexa' => $request->color_hexa,
        ]; 
    
        // Validator
        $validator = Validator::make($data , $rules );

        if ($validator->fails()) {
            return response()->json(formatMessage(404, $validator->messages()), 404);
        }

        try {
          
            $prod_colors  = DB::table($this->prodColor['table'])->where('id', $request->id_prodcolor )->get();

            if( ! $prod_colors[0] ):
                return response()->json(formatMessage(410, 'There is no such product registration with color variation!'), 410);
                die;
            endif;

            // Update table prod_colors
            DB::table($this->prodColor['table'])->where('id', $request->id_prodcolor )->update($data);

        } catch (\Exception $e) {

            if(config('app.debug')):
                return response()->json(formatMessage(1011, $e->getMessage()),  500);
            endif;

            return response()->json(formatMessage(1011, 'There was an error when performing the update operation'), 500);
        }

    }
}

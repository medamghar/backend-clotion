<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Photo;
use App\Models\Product;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Exceptions\AuthenticationException;
use PhpParser\Node\Stmt\Else_;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function multiProductDelete(Request $request){
        if ($request->has('data')) {
            $data = json_decode($request->input('data'), true);
            $products = Product::whereIn("id",$data["ids"])->pluck('id')->toArray();
            $true=true;
            $false=false;
            if(!empty($products)){
                $deletedRows= Product::whereIn("id",$data["ids"])->delete();
                if($deletedRows>0){
                    return response()->json(["ok"=>$true,"message"=>"The selected products have been deleted"],200);

                }
                return response()->json(["ok"=>$false , "message"=>"there was a problem while deleting"],400);

            }
        return response()->json(["ok"=>$false , "message"=>"products not found."],404);
        }
    }
    public function userSignUp(Request $request){
        try {
        $request->validate([
                "data.name" => "required|string|max:230",
                "data.email" => "required|email|string",
                "data.password" => "required|min:8|string|confirmed"
            ]);
            // $data = json_decode($request->input('data'),true);
            $email = $request->input('data.email');
$password = $request->input('data.password');
$name = $request->input('data.name');
            $user = User::create([
                "email" => $email,
                "password" => bcrypt($password), 
                "name" => $name
            ]); 
          
            if ($user) {
           
                return response()->json(["message" => "the user has been created succesfully"]);
            } else {
                
                return response()->json(["message" => "Authentication failed"]);
            }
        } catch (ValidationException $exception) {
       
            $errors = $exception->validator->errors()->all();
            return response()->json(['errors' => $errors], 422);
        }
        
        
       
        
    }
    public function userLogin(Request $request)
    {
     
            $ok = true;
            $Notok = false;
            $data = json_decode($request->input('data'), true);
            $email = $data['email'];
            $password =  $data['password'];
            $simo =Auth::attempt(['email' => $email, 'password' => $password]);
           if($simo){
            $user= Auth::user();
            $token = $user->createToken('token')->accessToken;

            return  response()->json(["ok"=>$ok,"ADM-TOKEN"=>$token],200);
           }
           else{
            return  response()->json(["ok"=>$Notok,"Message"=>"Email or password are invalid.","email"=>$request["email"]],445);

           }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSingleProduct($id)
    {
        $product=Product::with("Category")->find($id);
        if(!$product){
            return response()->json(["message"=>"Product not found"]);
        }
        return response()->json(["products"=>$product]);
  
    }
    public function userInfo()
    {
        
        if(Auth::guard("api")->check()){
         return response()->json(['user' => Auth::guard("api")->user()], 401);

        }
        else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function productUpdate(Request $request, $id){
        $product=Product::with("Category")->find($id);

        if ($request->has('data')) {
            $data = json_decode($request->input('data'), true);
            $cate = Category::find(intval($data["category"]));
          
            try {
                $product->title = $data["title"];       
                $product->description = $data["description"];
                $product->price = $data["price"];
                $product->colors = json_encode($data["colors"]);
                $product->size = json_encode($data["size"]);
                $product->category()->sync([$cate->id]);


        
                $product->save();
                $productUpdated = $product->wasChanged() || $product->category[0]->wasChanged();
            
      
                   
                    return response()->json(["ok" => true, "Message" => "The product has been updated."]);
                
            } catch (\Exception $e) {
                throw new Error($e);
            }
        }
    }
    public function getAllProducts(Request $request)
    {
        $rowsPerPage = $request->query('page', 5);
        $products = Product::with('Category','photos')->latest('created_at')->get();
        $counter = Product::with('Category')->count();
        return response()->json(["products"=>$products,"total"=>$counter]);
    }
    public function getAllCategories()
    {
        $Categories =Category::select('id', 'name')->get();
        return response()->json(["Categories"=>$Categories,]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function productDelete($id)
    {
        $product=Product::find($id);
        if($product){
            $ok = $product->delete();
            if($ok){
                return response()->json(["ok"=>$ok,"message"=>"product has been deleted."]);
    
            }
        return response()->json(["ok"=>$ok,"message"=>"there was an error while trying to delete product"]);
        
        }
        return response()->json(["ok"=>"false","message"=>"product not found"]);
    }
    public function prodctNew(Request $request)
    {
        $false=false;
        $true=true;

        if ($request->has('data')) {
            $data = json_decode($request->input('data'), true);
           
        try{
            $photo = $request->file("thumbnail");
            $filename = $photo->hashName();
            $photo->store("public/photos");
            $url = asset('storage/photos/'.$filename);
            $product_create =Product::create([
                "thumbnial"=>$url,
                "title"=> $data["title"],
                "description" =>$data["description"],
                "price" => $data["price"],
                'colors' => json_encode($data["colors"]),
                "size" => json_encode($data["size"]),
                


            ]) ;
        
        $category = Category::find(intval($data["category"]));
        $category->Product()->attach($product_create);
        $photos = $request->file("photos");
        foreach($photos as $photo){
            $filename = $photo->hashName();
            $photo->store("public/photos");
            $image = Photo::create([
                'url'=>asset('storage/photos/'.$filename)
            ]);
            $product_create->photos()->save($image);
        }
        if($product_create){
            return response()->json(["ok"=>$true,"Message"=>"the product has been created."]);

        }
        else{
            return response()->json(["ok"=>$false,"Message"=>"There was a problem while creating a problem."]);

        }
        }
        catch(\Exception $e){
            throw new Error($e);
        }
    }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}

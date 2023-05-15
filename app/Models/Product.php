<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable=[
        "title",
        "description",
        "price",
        'colors',
        "size",
        "thumbnial"

    ];
    use HasFactory;
    public function Category (){
        return $this->belongsToMany(Category::class,"Category_product");
    }
    public function Order (){
        return $this->belongsToMany(Order::class,"Order_product");
    }
    public function photos (){
        return $this->hasMany(Photo::class);
    }
}

<?php

namespace App\Traits;
use App\Exceptions;
trait CreateModelTrait
{
    public function createModelRecord($model,$elements){
        try{
            if(count($elements)>0){
                return $model::create($elements);
            }
        }catch(Exceptions $e){
            abort(500,'Page not found.');
        }
        
    }
}

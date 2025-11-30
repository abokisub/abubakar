<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\DB;
class MonnifyImport implements ToModel
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
    }
    
     public function model(array $row)
    {
        // Replace this with your actual import logic
        // file_put_contents('adex_res.json', '0' . $row[0]. ' 1 '. $row[1] . ' 2 '. $row[2]. ' 3 '. $row[3]);
        // if($row[1] == 'adex' || $row[1] == 'Adex' ){
        //     file_put_contents('adex_boy.json', '0' . $row[0]. ' 1 '. $row[1] . ' 2 '. $row[2]. ' 3 '. $row[3]); 
        // }
        $user = DB::table('user')->where(['rolex' => $row[3]])->first();
        if(!empty($user)){ 
            if($user->monify_ref == null){
            DB::table('user')->where('id', $user->id)->update(['monify_ref' => $row[0] ]);
            }
        }
    }
}

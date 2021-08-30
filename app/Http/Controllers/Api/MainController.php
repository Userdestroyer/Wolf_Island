<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Field;
use App\Models\Animal;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{

    function CreateAnimal($type, $pos_x, $pos_y)
    {
        $animal = new Animal();
        $field = Field::find(1);
        $animal->type = $type;
        $animal->pos_x = $pos_x;
        $animal->pos_y = $pos_y;
        
        if ($type == 'wolf') {
            $animal->hunger = $field->hunger;
        } else {
            $animal->hunger = 0;
        }

        $animal->save();
                    
    }

    //POST
    public function CreateField(Request $request) {

        //validation
        $request->validate([
            "x" => "required|numeric|not_in:0|gt:3",
            "y" => "required|numeric|not_in:0|gt:3",
            "hunger" => "required|numeric|not_in:0"
        ]);

        //create data
        if(!empty(DB::table('fields')->count())) {
            Field::truncate();
            if(!empty(DB::table('animals')->count())) {
                Animal::truncate();
            }
        }



        //create field
        $field = new Field();
        $field->x = $request->x;
        $field->y = $request->y;
        $field->hunger = $request->hunger;
        $field->iteration = 0;
        $field->save();


        // send response
        return response()->json([
            "status" => 1,
            "message" => "Field created successfully"
        ]);

    }

    //POST
    public function CreateOneAnimal(Request $request) {

        //validation

        if(!empty(DB::table('fields')->count())) {
        $field = Field::find(1);
        $animal = new Animal();
        $request->validate([
            "type" => "required",
            "pos_x" => "required|numeric|min:0",
            "pos_y" => "required|numeric|min:0"
        ]);

        if ($request->type == 'wolf'|| $request->type == 'hare') {
            if ($request->pos_x < $field->y && $request->pos_y < $field->y) {
            
                //create data
                $this->CreateAnimal($request->type, $request->pos_x, $request->pos_y);

                return response()->json([
                    "status" => 1,
                    "message" => "Animal created successfully"
                ]);

            } else {
                return response()->json([
                    "status" => 0,
                    "message" => "Location is out of field!"
                ], 409);
            }

        } else {
            return response()->json([
                "status" => 0,
                "message" => "Only values 'wolf' and 'hare' allowed on 'type' field"
            ], 409);
        }

        } else {
            return response()->json([
                "status" => 0,
                "message" => "No field found"
            ], 404);
        }
        
    }

    //POST
    public function CreateRandomAnimals(Request $request) {
        //validation
        if(!empty(DB::table('fields')->count())) {
            $field = Field::find(1);
            $animal = new Animal();
            $request->validate([
                "type" => "required",
                "number" => "required|numeric|min:0"
            ]);

            if ($request->type == 'wolf'|| $request->type == 'hare') {

                for ($i = 0; $i < $request->number; $i++) {
                    $rand_x = rand(0, ($field->x - 1));
                    $rand_y = rand(0, ($field->y - 1));
                    $this->CreateAnimal($request->type, $rand_x, $rand_y);
                }
                return response()->json([
                    "status" => 1,
                    "message" => "Animals created successfully"
                ]);
            } else {
                return response()->json([
                    "status" => 0,
                    "message" => "Only values 'wolf' and 'hare' allowed on 'type' field"
                ], 409);
            }

        } else {
            return response()->json([
                "status" => 0,
                "message" => "No field found"
            ], 404);
        }
    }

    //GET
    public function Update() {

        if(!empty(DB::table('animals')->count())) {

        if (Animal::where('type', 'hare')->exists()) {
        if (Animal::where('type', 'wolf')->exists()) {
        //WOLVES EAT HARES ON SAME SPOT

            $hunger = Field::where('id', 1)->value('hunger');
            $wolves = Animal::where('type', 'wolf')->get();
            $hares = Animal::where('type', 'hare')->get();
            foreach ($wolves as $wolf){
                foreach ($hares as $hare) {
                    if ($wolf->pos_x === $hare->pos_x && $wolf->pos_y === $hare->pos_y) {
                        $hare->delete();
                        $wolf->hunger = $hunger;
                        $wolf->save();
                    }
                }
            }

        //WOLVES CHECK NEIGHBOURS

            $wolves = Animal::where('type', 'wolf')->get();
            $hares = Animal::where('type', 'hare')->get();
            $tolerance = 1;



            foreach ($wolves as $wolf){
                $count = 0;
                //$hare_id = 0;
                //$wolf_id = 0;
                $pearls = Array();
                foreach ($hares as $hare) {
                    if (abs($wolf->pos_x - $hare->pos_x) <= $tolerance 
                        && abs($wolf->pos_y - $hare->pos_y) <= $tolerance 
                        && $wolf->pos_x . "x" . $wolf->pos_y != $hare->pos_x . "x" . $hare->pos_y) {
                            array_push($pearls, $hare->id);
                        }
                    }
                    if (count($pearls) > 0 && count($pearls) < 2) {
                        $hare = Animal::find($pearls[0]);
                        $hare->delete();
                        $wolf->hunger = $hunger;
                        $wolf->save();
                    }
                }
        }

        //HARES CHECK SAME SPOT

            $field = Field::find(1);
            $hares = Animal::where('type', 'hare')->get();
            $pearls = array();
            foreach ($hares as $hare) {
                $to_push = $hare->pos_x . "x" . $hare->pos_y;
                //echo $to_push . "\n";
                array_push($pearls, $to_push);
            }
            $pearls = array_count_values($pearls);
            //print_r($pearls);
            foreach ($pearls as $pearl_key => $pearl_value) {
                if ($pearl_value >= 2) {
                    $coordinates = explode('x', $pearl_key);

                    if (($coordinates[0] - 1) >= 0 && ($coordinates[1] - 1) >= 0) { $this->CreateAnimal('hare', $coordinates[0] - 1, $coordinates[1] - 1); }
                    if (($coordinates[0] - 1) >= 0 && ($coordinates[1] + 1) < $field->y) { $this->CreateAnimal('hare', $coordinates[0] - 1, $coordinates[1] + 1); }
                    if (($coordinates[0] + 1) < $field->x && ($coordinates[1] - 1) >= 0) { $this->CreateAnimal('hare', $coordinates[0] + 1, $coordinates[1] - 1); }
                    if (($coordinates[0] + 1) < $field->x && ($coordinates[1] + 1) < $field->y) { $this->CreateAnimal('hare', $coordinates[0] + 1, $coordinates[1] + 1); }
                }
            }
        }

        //MOVE ALL

        $field = Field::find(1);
        $animals = Animal::get();
            foreach($animals as $animal) {
                do {
                    $i = rand(0, 3);
                    $done = false;
                    switch ($i) {
                        case 0:
                            if (($animal->pos_x + 1) < $field->x) {$animal->pos_x++; $done = true;}
                            break;
                        case 1:
                            if (($animal->pos_x - 1) >= 0) {$animal->pos_x--; $done = true;}
                            break;
                        case 2:
                            if (($animal->pos_y + 1) < $field->y) {$animal->pos_y++; $done = true;}
                            break;
                        case 3:
                            if (($animal->pos_y - 1) >= 0) {$animal->pos_y--; $done = true;}
                            break;      
                    }
                } while ($done === false);
                if ($animal->type === "wolf") { $animal->hunger--;}
                $animal->save();
                if ($animal->type === "wolf" && $animal->hunger < 1 ) {$animal->delete();}
                $field->iteration++;
            }

            $battlefield = Animal::get();
            return response()->json([
                "status" => 1,
                "message" => "Listing battlefield",
                "data" => $battlefield
            ], 200);
        } else {
            return response()->json([
                "status" => 0,
                "message" => "No animals on field"
            ], 404);
        }

    }

    //GET
    public function ListField() {
        $field = Field::get();

        return response()->json([
            "status" => 1,
            "message" => "Listing Fields",
            "data" => $field
        ], 200);
    }

    public function Battlefield() {
        $animal = Animal::get();

        return response()->json([
            "status" => 1,
            "message" => "Listing battlefield",
            "data" => $animal
        ], 200);
    }

    public function ShowMe() {
        for ($i = 0; $i < 8; $i++) {
            echo $i . " \n";
        }
    }

}

<?php

namespace CupNoodles\QuickStock\Controllers;

use AdminMenu;
use DB;

use Admin\Models\Locations_model;
use Admin\Models\Categories_model;
use Admin\Models\Menus_model;
use Admin\Traits\Locationable;

class QuickStock extends \Admin\Classes\AdminController
{
   
    use Locationable;
    //protected $requiredPermissions = 'Admin.UnitsOfMeasure';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('quickstock', 'restaurant');
    }

    public function view($route)
    {
        $this->vars['locations'] = $this->getLocations();


        $this->addJS('extensions/cupnoodles/quickstock/assets/js/quickstock.js', 'cupnoodles-quickstock');

    }

    // ajax handler for quick86 view
    public function save($route){
        $post = post();

        if($post['action'] == 'out_of_stock'){
            if($post['val'] == 'false'){// false means out of stock
                DB::table('locationables')->insert([
                    'location_id'      => $post['location_id'],
                    'locationable_id'             => $post['menu_id'],
                    'locationable_type' => 'menu_out_of_stock'
                ]);
            }
            else{
                DB::table('locationables')
                ->where('location_id', $post['location_id'])
                ->where('locationable_id', $post['menu_id'])
                ->where('locationable_type', 'menu_out_of_stock')
                ->delete();
            }
        }
        elseif($post['action'] == 'status'){
            $menu = Menus_model::where('menu_id', $post['menu_id'])->update(['menu_status' => $post['val'] == 'true' ? 1 : 0]);
        }



        $response = [];
        $response = $post;
        echo json_encode($response);
        die();
    }
    public function getLocations(){

        $list = Locations_model::isEnabled()->get();
        foreach($list as $k=>$location){
            $list[$k]->categories = $this->getCategories($location->location_id);
        }
        return $list;

    }

    public function getCategories($location_id){

        $list = Categories_model::isEnabled()->orderBy('priority')->with(['menus'])->get();


        // set oos on each menu item
        foreach($list as $key=>$value){

            foreach($value->menus as $k=>$menu){
                $tmp = $list[$key]->menus[$k];
                $tmp->out_of_stock = DB::table('locationables')
                ->where('location_id', $location_id)
                ->where('locationable_id', $menu->menu_id)
                ->where('locationable_type', 'menu_out_of_stock')
                ->count();
                $list[$key]->menus[$k] = $tmp;
                
            }

        }

        return $list;
    }





}

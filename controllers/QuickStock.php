<?php

namespace CupNoodles\QuickStock\Controllers;

use AdminMenu;
use DB;

use Admin\Models\Locations_model;
use Admin\Models\Categories_model;
use Admin\Models\Menus_model;
use Admin\Traits\Locationable;
use Admin\Facades\AdminAuth;
use Admin\Models\Staffs_model;

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

        // set a default location here - AdminLocation::getId() doesn't work for Super Users so get it from the DB
        $admin_id = AdminAuth::getId(); 
        foreach($this->vars['locations']  as $k=>$location){
            $staffs = Staffs_model::whereHasLocation($location->getKey())->get();
            foreach($staffs as $staff){
                if($staff->getKey() == $admin_id){
                    $this->vars['locations'][$k]->active = true;
                }
            }
        }
        
        
        
        
        //$this->vars['admin_location'] = AdminLocation::getId();

        $this->addJS('extensions/cupnoodles/quickstock/assets/js/quickstock.js', 'cupnoodles-quickstock');


        $this->addJs('~/app/system/assets/ui/js/vendor/moment.min.js', 'moment-js');
        $this->addCss('~/app/admin/formwidgets/datepicker/assets/vendor/datepicker/bootstrap-datepicker.min.css', 'bootstrap-datepicker-css');
        $this->addJs('~/app/admin/formwidgets/datepicker/assets/vendor/datepicker/bootstrap-datepicker.min.js', 'bootstrap-datepicker-js');
        if (setting('default_language') != 'en')
            $this->addJs('~/app/admin/formwidgets/datepicker/assets/vendor/datepicker/locales/bootstrap-datepicker.'.strtolower(str_replace('_', '-', setting('default_language'))).'.min.js', 'bootstrap-datepicker-js');
    
        $this->addCss('~/app/admin/formwidgets/datepicker/assets/css/datepicker.css', 'datepicker-css');
        $this->addJs('~/app/admin/formwidgets/datepicker/assets/js/datepicker.js', 'datepicker-js');
    }

    // ajax handler for quick86 view
    public function save($route){
        $post = post();

        // chrome doesn't send disabled form elements until after they're activated, so default to forever if blank
        if(!isset($post['in_stock_date'])){
            $post['in_stock_date'] = '';
        }

        if($post['action'] == 'out_of_stock'){
            $locationable_id = $post['menu_id'];
            $locationable_type = 'menu_out_of_stock';
            if(isset($post['option_value'])){
                $locationable_id = $post['option_value'];
                $locationable_type = 'option_value_out_of_stock';
            }
            if($post['val'] == 'false'){// false means out of stock
                DB::table('locationables')->updateOrInsert([
                    'location_id'       => $post['location_id'],
                    'locationable_id'   => $locationable_id,
                    'locationable_type' => $locationable_type
                ],
                    [
                    'options' => $post['in_stock_date']
                    ]);
            }
            else{
                DB::table('locationables')
                ->where('location_id', $post['location_id'])
                ->where('locationable_id', $locationable_id)
                ->where('locationable_type', $locationable_type )
                ->delete();
            }
        }

        $response = $post;
        $response['in_stock_date_text'] = $this->dateTextFromDate($response['in_stock_date']);
        echo json_encode($response);
        die();
    }
    public function getLocations(){

        $list = Locations_model::isEnabled()->get();
        foreach($list as $l=>$location){
            $list[$l]->categories = $this->getCategories($location->location_id);
        }
        return $list;

    }

    public function getCategories($location_id){

        $list = Categories_model::isEnabled()->orderBy('priority')->with([
            'menus', 
            'menus.menu_options', 
            'menus.menu_options.menu_option_values',
            'menus.menu_options.option_values'])->get();

        // TODO order the list and display by print docket name (if set in settings)


        // set oos on each menu item
        foreach($list as $key=>$category){

            foreach($category->menus as $k=>$menu){
                $tmp = $list[$key]->menus[$k];
                $out_of_stock = DB::table('locationables')
                ->where('location_id', $location_id)
                ->where('locationable_id', $menu->menu_id)
                ->where('locationable_type', 'menu_out_of_stock')
                ->get();
                
                $tmp->out_of_stock = count($out_of_stock);
                if(count($out_of_stock)){
                    $tmp->in_stock_date = $out_of_stock->first()->options;
                }
                else{
                    $tmp->in_stock_date = date('m/d/Y', strtotime("+1 day"));
                }
                $tmp->in_stock_date_text = $this->dateTextFromDate($tmp->in_stock_date);
                $list[$key]->menus[$k] = $tmp;

                // set oos on each option value
                foreach($menu['menu_options'] as $j=>$option){
                    foreach($option['option_values'] as $l=>$option_value){
                        $tmp_val = $list[$key]->menus[$k]->menu_options[$j]->option_values[$l];
                        $out_of_stock = DB::table('locationables')
                        ->where('location_id', $location_id)
                        ->where('locationable_id', $option_value->option_value_id)
                        ->where('locationable_type', 'option_value_out_of_stock')
                        ->get();
                        
                        $tmp_val->out_of_stock = count($out_of_stock);
                        if(count($out_of_stock)){
                            $tmp_val->in_stock_date = $out_of_stock->first()->options;
                        }
                        else{
                            $tmp_val->in_stock_date = date('m/d/Y', strtotime("+1 day"));
                        }
                        $tmp_val->in_stock_date_text = $this->dateTextFromDate($tmp_val->in_stock_date);
                        $list[$key]->menus[$k]->menu_options[$j]->option_values[$l] = $tmp_val;
                    }
                }
            }
        }



        return $list;
    }

    public function dateTextFromDate($date){
        if($date == ''){
            return lang('cupnoodles.quickstock::default.forever');
        }
        elseif($date == date('m/d/Y', strtotime("+1 day")) ) {
            return lang('cupnoodles.quickstock::default.tomorrow');
        }
        else{
            return $date;
        }
    }





}

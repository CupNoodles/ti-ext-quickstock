<?php 

namespace CupNoodles\QuickStock;

use Admin\Models\Menus_model;
use Admin\Models\Menu_item_option_values_model;
use System\Classes\BaseExtension;
use DB;
use Event;
use App;
use Igniter\Flame\Exception\ApplicationException;

/**
 * Butcher Extension Information File
 */
class Extension extends BaseExtension
{


    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {

        // this allows templates to access the isOutOfStock() function
        Menus_Model::extend(function ($model) {
            $model->addDynamicMethod('isOutOfStock', function($location_id, $orderDateTime) use ($model) {
                $oos = DB::table('locationables')
                ->where('location_id', $location_id)
                ->where('locationable_id', $model->menu_id)
                ->where('locationable_type', 'menu_out_of_stock')
                ->get();
                if(count($oos)){
                    if($oos[0]->options == ''){
                        return true;
                    }
                    if(strtotime($oos[0]->options) <= $orderDateTime->timestamp){
                        return false;
                    }
                    else{
                        return true;
                    }
                }
                else{
                    return false;
                }
            });
        });

        // this allows templates to access the isOutOfStock() function
        Menu_item_option_values_model::extend(function ($model) {
            $model->addDynamicMethod('isOutOfStock', function($location_id, $orderDateTime) use ($model) {
                $oos = DB::table('locationables')
                ->where('location_id', $location_id)
                ->where('locationable_id', $model->option_value_id)
                ->where('locationable_type', 'option_value_out_of_stock')
                ->get();
                if(count($oos)){
                    if($oos[0]->options == ''){
                        return true;
                    }
                    if(strtotime($oos[0]->options) <= $orderDateTime->timestamp){
                        return false;
                    }
                    else{
                        return true;
                    }
                }
                else{
                    return false;
                }
            });
        });

        // error when adding out of stock to cart
        Event::listen('cart.adding', function ($action, $cartItem){
            if($cartItem->model->isOutOfStock(app('location')->getId(), app('location')->orderDateTime())){
                unset($cartItem);
                throw new ApplicationException(lang('cupnoodles.quickstock::default.item_out_of_stock_error'));
            }
        });

        
        Event::listen('igniter.checkout.beforeSaveOrder', function ($order, $data){
            
            foreach($order->cart as $key=>$cartItem){
                if($cartItem->model->isOutOfStock(app('location')->getId(), app('location')->orderDateTime())){
                    throw new ApplicationException($cartItem->name . ' ' . lang('cupnoodles.quickstock::default._out_of_stock_error'));
                }    
            }

        });





    }


    public function registerSchedule($schedule)
    {

        // delete any out_of_stock locationables for today or earlier
        $schedule->call(function () {
            $out_of_stocks = DB::table('locationables')
                ->where('locationable_type', 'menu_out_of_stock')
                ->get();
            
            foreach($out_of_stocks as $k=>$oos){

                if( $oos->options != ''){
                    if(strtotime($oos->options) <= time()){
                        DB::table('locationables')
                        ->where('locationable_type', 'menu_out_of_stock')
                        ->where('location_id', $oos->location_id)
                        ->where('locationable_id', $oos->locationable_id)
                        ->delete();
                    }
                }
                
            }
            $out_of_stocks_option_values = DB::table('locationables')
                ->where('locationable_type', 'option_value_out_of_stock')
                ->get();
            
            foreach($out_of_stocks as $k=>$oos){

                if( $oos->options != ''){
                    if(strtotime($oos->options) <= time()){
                        DB::table('locationables')
                        ->where('locationable_type', 'option_value_out_of_stock')
                        ->where('location_id', $oos->location_id)
                        ->where('locationable_id', $oos->locationable_id)
                        ->delete();
                    }
                }
                
            }
        })->hourly();

    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Quickstock Settings',
                'description' => 'Manage quickstock settings',
                'icon' => 'fa fa-balance-scale',
                'model' => 'CupNoodles\QuickStock\Models\QuickStockSettings',
                'permissions' => ['Module.QuickStock'],
            ],
        ];
    }


    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'Admin.UnitsOfMeasure' => [
                'label' => 'cupnoodles.quickstock::default.permissions',
                'group' => 'admin::lang.permissions.name',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'restaurant' => [
                'child' => [
                    'quickstock' => [
                        'priority' => 20,
                        'class' => 'pages',
                        'href' => admin_url('cupnoodles/quickstock/quickstock/view'),
                        'title' => lang('cupnoodles.quickstock::default.side_menu'),
                        'permission' => 'Admin.QuickStock',
                    ],
                ],
            ],
        ];
    }
}

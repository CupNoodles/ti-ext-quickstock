<?php

namespace CupNoodles\QuickStock\Models;

use Admin\Traits\Locationable;

use Model;

class QuickStock extends Model{

    use Locationable;

    protected $table = 'menus';

    protected $primaryKey = 'menus_id';
}
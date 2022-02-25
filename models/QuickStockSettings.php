<?php

namespace CupNoodles\QuickStock\Models;

use Model;

/**
 * @method static instance()
 */
class QuickStockSettings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'cupnoodles_quickstock_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'quickstocksettings';

    //
    //
    //
}

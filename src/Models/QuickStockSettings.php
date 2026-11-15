<?php

declare(strict_types=1);

namespace CupNoodles\QuickStock\Models;

use Igniter\Flame\Database\Model;
use Igniter\System\Actions\SettingsModel;

/**
 * Quick 86 page options. Setting keys are unchanged from v3 so saved values carry over.
 *
 * @mixin SettingsModel
 */
class QuickStockSettings extends Model
{
    public array $implement = [SettingsModel::class];

    public string $settingsCode = 'cupnoodles_quickstock_settings';

    public string $settingsFieldsConfig = 'quickstocksettings';
}

<?php
namespace App;

use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Cache;

trait RefreshesInventarioCache
{
    public static function bootRefreshesInventarioCache()
    {
        static::created(function ($model) {
            $model->forgetCachedInventario();
        });
        static::updated(function ($model) {
            $model->forgetCachedInventario();
        });
        static::deleted(function ($model) {
            $model->forgetCachedInventario();
        });
    }
    /**
     *  Forget the cached permissions.
     */
    public function forgetCachedInventario()
    {
        Cache::flush();
    }
}

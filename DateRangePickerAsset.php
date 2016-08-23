<?php
/**
 * @author Bogdan Burim <bgdn2007@ukr.net>
 * @author Marek Petras <mark@markpetras.eu>
 */

namespace marekpetras\daterangepicker;

use yii\web\AssetBundle;
use yii;

class DateRangePickerAsset extends AssetBundle
{
	public static $extra_js = [];
    public $sourcePath = '@daterangepicker/assets';
    public $css = [
        'bootstrap-daterangepicker/daterangepicker.css'
    ];
    public $js = [
        'bootstrap-daterangepicker/daterangepicker.js',
        'date.js',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset',
    ];

	public function init()
    {
		Yii::setAlias('@daterangepicker', __DIR__);

		foreach (static::$extra_js as $js_file) {
			array_unshift($this->js, $js_file);
		}

		return parent::init();
	}
}
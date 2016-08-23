<?php
/**
 * @author Bogdan Burim <bgdn2007@ukr.net>
 * @author Marek Petras <mark@markpetras.eu>
 */

namespace marekpetras\daterangepicker;

use Yii;
use DateTime;
use DateInterval;
use yii\base\Model;
use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\base\Widget as Widget;
use yii\web\JsExpression;
use yii\helpers\ArrayHelper;

class DateRangePicker extends Widget
{
    /**
     * @var string $selector
     */
    public $selector;

    /**
     * @var string JS Callback for Daterange picker
     */
    public $callback;

    /**
     * @var array Options to be passed to daterange picker
     */
    public $options = [];

    /**
     * @var array the HTML attributes for the widget container.
     */
    public $htmlOptions = [];

    /**
     * @var array the default HTML attributes for the widget container.
     */
    public $defaultHtmlOptions     = [
        'placeholder' => 'Select a daterange...',
        'class' => 'form-control',
    ];

    /**
     * @var array the default Options to be passed to daterange picker
     */
    public $defaultOptions = [
        'alwaysShowCalendars' => true,
        'autoApply' => true,
    ];

    /**
     * @var array display date javascript format
     */
    public $displayFormat = 'D MMMM YYYY';

    /**
     * @var array request date javascript format
     */
    public $requestFormat = 'YYYY-MM-DD';

    /**
     * @var bool whether to include moment.js
     */
    public $moment = true;

    /**
     * @var bool whether to add hidden inputs to store range values
     */
    public $addInputs = true;

    /**
     * @var string start date hidden input name
     */
    public $inputFromName = 'DateFrom';

    /**
     * @var string end date hidden input name
     */
    public $inputToName = 'DateTo';

    /**
     * @var string default start date for hidden input
     */
    public $inputFromDefault;

    /**
     * @var string start date hidden input id
     */
    public $inputFromId = 'dateFrom';

    /**
     * @var string end date hidden input id
     */
    public $inputToId = 'dateTo';

    /**
     * @var string default end date for hidden input
     */
    public $inputToDefault;

    /**
     * @inheritdoc
     */
    public function init()
    {
        //checks for the element id
        if (!isset($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = $this->getId();
        }

        if ( !isset($this->options['ranges']) ) { $this->options['ranges'] = new JsExpression("{
                    'Today'        : [Date.today(), Date.today()],
                    'Yesterday'    : [Date.today().add({ days: -1 }), Date.today().add({ days: -1 })],
                    'Last 7 Days'  : [Date.today().add({ days: -7 }), Date.today().add({ days: -1 })],
                    'Last 30 Days' : [Date.today().add({ days: -30 }), Date.today().add({ days: -1 })],
                    'Last Month'   : [Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 })],
                    'Month to Date': [Date.today().moveToFirstDayOfMonth(), Date.today().add({ days: -1 })],
                    'Current Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
                    'Year to Date' : [Date.today().moveToMonth(0,-1).moveToFirstDayOfMonth(), Date.today()],
                    'Current Year' : [Date.today().moveToMonth(0,-1).moveToFirstDayOfMonth(), Date.today().moveToMonth(11).moveToLastDayOfMonth()]
                }");
        }

        if ( !$this->callback ) {
            $this->callback = new JsExpression("function(start, end, label){
                $('#".$this->inputFromId."').val(start.format('".$this->requestFormat."'));
                $('#".$this->inputToId."').val(end.format('".$this->requestFormat."'));
                $('#".$this->id."').val(start.format('".$this->displayFormat."') + ' - ' + end.format('".$this->displayFormat."'));
            }");
        }

        if ( $this->displayFormat && !isset($this->options['locale']['format']) ) {
            $this->defaultOptions['locale']['format'] = $this->displayFormat;
        }

        if ( !isset($this->options['startDate']) ) {
            $this->options['startDate'] = new JsExpression('Date.today().add({ days: -7 })');
        }

        if ( !isset($this->options['endDate']) ) {
            $this->options['endDate'] = new JsExpression('Date.today().add({ days: -1 })');
        }

        $this->htmlOptions = ArrayHelper::merge($this->defaultHtmlOptions, $this->htmlOptions);
        $this->options = ArrayHelper::merge($this->defaultOptions, $this->options);


        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerPlugin();
    }

    protected function registerPlugin()
    {
        if ($this->moment) {
            DateRangePickerAsset::$extra_js[] = defined('YII_DEBUG') && YII_DEBUG ? 'bootstrap-daterangepicker/moment.js' : 'bootstrap-daterangepicker/moment.min.js';
        }

        if ($this->selector) {
            $this->registerJs($this->selector, $this->options, $this->callback);
        }
        else {

            // render the date range input field
            $id = $this->htmlOptions['id'];
            echo Html::tag('input', '', $this->htmlOptions);
            $this->registerJs("#{$id}", $this->options, $this->callback);

            // render hidden inputs and fill them with default values
            if ( $this->addInputs ) {
                echo Html::hiddenInput($this->inputFromName,$this->inputFromDefault,['id'=>$this->inputFromId]);
                echo Html::hiddenInput($this->inputToName,$this->inputToDefault,['id'=>$this->inputToId]);

                $this->getView()->registerJs("
                    $('#".$this->inputFromId."').val($('#" . $id . "').data('daterangepicker').startDate.format('".$this->requestFormat."'));
                    $('#".$this->inputToId."').val($('#" . $id . "').data('daterangepicker').endDate.format('".$this->requestFormat."'));
                ");
            }
        }
    }

    protected function registerJs($selector, $options, $callback)
    {
        $view = $this->getView();

        DateRangePickerAsset::register($view);

        $js   = [];
        $js[] = '$("' . $selector . '").daterangepicker(' . Json::encode($options) . ($callback ? ', ' . Json::encode($callback) : '') . ');';
        $view->registerJs(implode("\n", $js),View::POS_READY);
    }
}
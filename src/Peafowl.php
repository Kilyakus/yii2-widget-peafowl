<?php
namespace kilyakus\peafowl;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/*
    For example:

    echo Peafowl::widget([
        'items' => [
            [
                'icon' => 'fa fa-warning',
                'badge' => Badge::widget(['label' => 'New', 'round' => false]),
                'label' => 'Home',
                'url' => ['site/index'],
                'linkOptions' => [...],
            ],
            [
                'label' => 'Dropdown',
                'items' => [
                     ['label' => 'Level 1 - Dropdown A', 'url' => '#'],
                     '<li class="divider"></li>',
                     '<li class="dropdown-header">Dropdown Header</li>',
                     ['label' => 'Level 1 - Dropdown B', 'url' => '#'],
                ],
            ],
        ],
    ]);

    Note: Multilevel dropdowns beyond Level 1 are not supported in Bootstrap 3.

*/

class Peafowl extends \yii\bootstrap\Nav
{
    public $pluginSupport = false;

    /**
     * Positions
     */
    const POS_DEFAULT = '';
    const POS_LEFT = 'pull-left';
    const POS_RIGHT = 'pull-right';

    /**
     * Types
     */
    const TYPE_DEFAULT = '';
    const TYPE_NOTIFICATION = 'notification';
    const TYPE_INBOX = 'inbox';
    const TYPE_TASKS = 'tasks';
    const TYPE_USER = 'user';

    /**
     * Navbars
     */
    const NAVBAR_NONE = '';
    const NAVBAR_DEFAULT = 'kt-nav';

    /**
     * Items
     */
    const ITEM_DIVIDER = 'divider';

    /**
     * @var array list of items in the nav widget. Each array element represents a single
     * menu item which can be either a string or an array with the following structure:
     *
     * - label: string, required, the nav item label.
     * - icon: string, optional, the nav item icon.
     * - badge: array, optional
     * - url: optional, the item's URL. Defaults to "#".
     * - visible: boolean, optional, whether this menu item is visible. Defaults to true.
     * - linkOptions: array, optional, the HTML attributes of the item's link.
     * - options: array, optional, the HTML attributes of the item container (LI).
     * - active: boolean, optional, whether the item should be on active state or not.
     * - items: array|string, optional, the configuration array for creating a [[Dropdown]] widget,
     *   or a string representing the dropdown menu. Note that Bootstrap does not support sub-dropdown menus.
     *
     * If a menu item is a string, it will be rendered directly without HTML encoding.
     */
    public $items = [];

    /**
     * @var string the nav position
     */
    public $position = self::POS_DEFAULT;

    /**
     * @var string dropdownType
     */
    public $dropdownType = self::TYPE_DEFAULT;

    /**
     * @var string navbar holder
     */
    public $navbar = self::NAVBAR_DEFAULT;

    /**
     * Initializes the widget.
     */
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        PeafowlAsset::register($this->getView());
        return $this->renderItems();
    }

    /**
     * Renders a widget's item.
     * @param string|array $item the item to render.
     * @return string the rendering result.
     * @throws InvalidConfigException
     */
    public function renderItem($item)
    {
        if (is_string($item))
        {
            return $item;
        }

        if (array_key_exists(self::ITEM_DIVIDER, $item))
        {
            return Html::tag('li', '', ['class' => self::ITEM_DIVIDER]);
        }

        $items = ArrayHelper::getValue($item, 'items');
        
        if ($items === null)
        {
            return parent::renderItem($item);
        }

        if (!isset($item['label']) && !isset($item['icon']))
        {
            throw new InvalidConfigException("The 'label' option is required.");
        }
        
        $dropdownType = ArrayHelper::getValue($item, 'dropdownType', self::TYPE_DEFAULT);
        $options = ArrayHelper::getValue($item, 'options', []);

        Html::addCssClass($options, 'dropdown');

        if ($dropdownType !== self::TYPE_DEFAULT)
        {
            if ($dropdownType !== self::TYPE_USER)
            {
                Html::addCssClass($options, 'dropdown-extended');
            }

            Html::addCssClass($options, 'dropdown-'.$dropdownType);

            // yii2-template-engine - задумка сделать смену дизайна и возможность настроить связь между всеми виджетами
            // if (Engine::HEADER_DROPDOWN_DARK === Engine::getComponent()->headerDropdown)
            // {
            //     Html::addCssClass($options, 'dropdown-dark');
            // }
        }

        if (isset($item['active']))
        {
            $active = ArrayHelper::remove($item, 'active', false);
        }
        else
        {
            $active = $this->isItemActive($item);
        }

        if ($active)
        {
            Html::addCssClass($options, 'active');
        }
        
        return Html::tag('li', sprintf('%s%s', $this->_getLinkTag($item), $this->_getDropdownTag($item)), $options);
    }

    /**
     * Retrieves link tag
     * @param array $item given item
     * @return string link
     */
    private function _getLinkTag($item)
    {
        $dropdownType = ArrayHelper::getValue($item, 'dropdownType', self::TYPE_DEFAULT);

        if ($dropdownType !== self::TYPE_DEFAULT)
        {
            $label = $item['label'];
        }
        else
        {
            $label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];
        }

        $icon = ArrayHelper::getValue($item, 'icon', null);

        if ($icon)
        {
            if ($dropdownType === self::TYPE_USER)
            {
                $label .= Html::tag('i', '', ['class' => $icon]);
            }
            else
            {
                $label = Html::tag('i', '', ['alt' =>$label, 'class' => $icon]);
            }
        }

        $label .= ArrayHelper::getValue($item, 'badge', '');

        $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);

        $linkOptions['data-toggle'] = 'dropdown';
        $linkOptions['data-hover'] = 'dropdown';
        $linkOptions['data-close-others'] = 'true';

        Html::addCssClass($linkOptions, 'dropdown-toggle');

        $url = ArrayHelper::getValue($item, 'url', false);

        if (!$url)
        {
            return Html::a($label, 'javascript:;', $linkOptions);
        }

        return Html::a($label, Url::toRoute(ArrayHelper::getValue($item, 'url', '#')), $linkOptions);
    }

    /**
     * Retrieves items tag
     * @param array $item given parent item
     * @return Dropdown widget
     */
    private function _getDropdownTag($item)
    {
        $dropdownType = ArrayHelper::getValue($item, 'dropdownType', self::TYPE_DEFAULT);

        $items = ArrayHelper::getValue($item, 'items', null);

        if ($items !== null && is_array($items))
        {
            if ($dropdownType === self::TYPE_DEFAULT || $dropdownType === self::TYPE_USER)
            {
                $options = ['class' => 'dropdown-menu-default'];
            }
            else
            {
                $options = ['class' => sprintf('%s %s', 'dropdown-menu-default extended', $dropdownType)];
            }

            $items = Dropdown::widget([
                    'title' => ArrayHelper::getValue($item, 'title', ''),
                    'more' => ArrayHelper::getValue($item, 'more', []),
                    'scroller' => ArrayHelper::getValue($item, 'scroller', []),
                    'items' => $items,
                    'encodeLabels' => $this->encodeLabels,
                    'clientOptions' => false,
                    'options' => $options,
            ]);
        }

        return $items;
    }

    /**
     * Renders user item.
     * @param $label string User label
     * @param $photo string User photo url
     * @return string the rendering result
     */
    public static function userItem($label, $photo)
    {
        $lines = [];
        $lines[] = Html::tag('span', $label, ['class' => 'username username-hide-on-mobile']);
        $lines[] = Html::img($photo, ['alt' => $label, 'class' => 'img-circle']);
        return implode("\n", $lines);
    }
}
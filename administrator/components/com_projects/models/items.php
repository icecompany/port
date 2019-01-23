<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelItems extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id',
                'i.title_ru',
                'section',
                'price',
                'search',
            );
        }
        parent::__construct($config);
    }

    public static function getInstance($type='Items', $prefix = 'ProjectsModel', $config = array())
    {
        return parent::getInstance($type, $prefix, $config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`i`.`id`, `i`.`title_ru`, `i`.`title_en`, `i`.`unit`, IFNULL(`i`.`unit_2`,'TWO_NOT_USE') as `unit_2`, `i`.`state`")
            ->select("`p`.`title` as `price`, `s`.`title` as `section`")
            ->from('`#__prc_items` as `i`')
            ->leftJoin("`#__prc_sections` as `s` ON `s`.`id` = `i`.`sectionID`")
            ->leftJoin("`#__prc_prices` as `p` ON `p`.`id` = `s`.`priceID`");

        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('(`i`.`title_ru` LIKE ' . $search. ' OR `i`.`title_en` LIKE ' . $search . ')');
        }
        // Фильтруем по прайсу.
        $price = $this->getState('filter.price');
        if (is_numeric($price))
        {
            $query->where('`s`.`priceID` = ' . (int) $price);
        }
        // Фильтруем по разделу.
        $section = $this->getState('filter.section');
        if (is_numeric($section))
        {
            $query->where('`i`.`sectionID` = ' . (int) $section);
        }

        /* Сортировка */
        $orderCol  = $this->state->get('list.ordering', '`i`.`title`');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array();
        foreach ($items as $item) {
            $arr['id'] = $item->id;
            $url = JRoute::_("index.php?option=com_projects&amp;view=item&amp;layout=edit&amp;&id={$item->id}");
            $link = JHtml::link($url, $item->title_ru ?? $item->title_en);
            $arr['price'] = $item->price;
            $arr['section'] = $item->section;
            $arr['title'] = $link;
            $arr['unit'] = ProjectsHelper::getUnit($item->unit);
            $arr['unit_2'] = ProjectsHelper::getUnit($item->unit_2);
            $arr['state'] = $item->state;
            $result[] = $arr;
        }
        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $price = $this->getUserStateFromRequest($this->context . '.filter.price', 'filter_price', '', 'string');
        $this->setState('filter.price', $price);
        $section = $this->getUserStateFromRequest($this->context . '.filter.section', 'filter_section', '', 'string');
        $this->setState('filter.section', $section);
        parent::populateState('`i`.`title_ru`', 'asc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.price');
        $id .= ':' . $this->getState('filter.section');
        return parent::getStoreId($id);
    }
}
<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelSections extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 's.id',
                'search',
                'price',
                's.title',
            );
        }
        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`s`.`id`, `p`.`title` as `price`, `s`.`title`, `s`.`state`")
            ->from('`#__prc_sections` as `s`')
            ->leftJoin("`#__prc_prices` as `p` ON `p`.`id` = `s`.`priceID`")
            ->order("`s`.`title`");

        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->q("%{$search}%");
            $query->where("s.title LIKE {$search}");
        }
        // Фильтруем по прайсу.
        $price = $this->getState('filter.price');
        if (is_numeric($price)) {
            $price = (int) $price;
            $query->where("s.priceID = {$price}");
        }
        //Фильтр по проекту
        $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $priceID = ProjectsHelper::getProjectPrice($project);
            $query->where("s.priceID = {$priceID}");
        }

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array();
        foreach ($items as $item) {
            $arr['id'] = $item->id;
            $url = JRoute::_("index.php?option=com_projects&amp;task=section.edit&amp;&id={$item->id}");
            $link = JHtml::link($url, $item->title);
            $arr['price'] = $item->price;
            $arr['title'] = $link;
            $arr['state'] = $item->state;
            $result[] = $arr;
        }
        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $price = $this->getUserStateFromRequest($this->context . '.filter.price', 'filter_price', '', 'string');
        $this->setState('filter.search', $search);
        $this->setState('filter.price', $price);
        parent::populateState('s.title', 'asc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.price');
        return parent::getStoreId($id);
    }
}
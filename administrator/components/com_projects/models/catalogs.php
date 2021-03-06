<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelCatalogs extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'number',
                'square',
                'catalog',
                'cattitle',
                'category',
                'unit',
                'search',
                'hotel',
            );
        }
        parent::__construct($config);
    }

    public static function getInstance($type='Catalogs', $prefix = 'ProjectsModel', $config = array())
    {
        return parent::getInstance($type, $prefix, $config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`cat`.`id`, IFNULL(IFNULL(`cat`.`number`,`cat`.`title`),`cat`.`gos_number`) as `number`, `cat`.`square`")
            ->select("`t`.`title` as `catalog`, `t`.`id` as `catalogID`")
            ->select("`n`.`title_ru` as `category`")
            ->select("`h`.`title_ru` as `hotel`")
            ->from("`#__prj_catalog` as `cat`")
            ->leftJoin("`#__prj_catalog_titles` as `t` ON `t`.`id` = `cat`.`titleID`")
            ->leftJoin("`#__prj_hotels_number_categories` as `n` ON `n`.`id` = `cat`.`categoryID`")
            ->leftJoin("`#__prj_hotels` as `h` ON `h`.`id` = `n`.`hotelID`");

        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $search = $db->q("%{$search}%");
            $query->where("(`cat`.`number` LIKE {$search} OR `cat`.`gos_number` LIKE  {$search})");
        }
        // Фильтруем по каталогу стендов.
        $cattitle = $this->getState('filter.cattitle');
        if (is_numeric($cattitle)) {
            $query->where('`cat`.`titleID` = ' . (int) $cattitle);
        }
        //Фильтр по глобальному проекту
        $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $cid = ProjectsHelper::getProjectCatalog($project);
            $query->where('`cat`.`titleID` = ' . (int)$cid);
        }

        /* Сортировка */
        $orderCol  = $this->state->get('list.ordering', 'number');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array();
        $return = base64_encode("index.php?option=com_projects&view=catalogs");
        foreach ($items as $item) {
            $arr = array();
            $arr['id'] = $item->id;
            $url = JRoute::_("index.php?option=com_projects&amp;task=catalog.edit&amp;&id={$item->id}");
            $text = $item->number;
            $link = JHtml::link($url, $text);
            $arr['number'] = (!ProjectsHelper::canDo('projects.access.catalogs')) ? $item->price : $link;
            $url = JRoute::_("index.php?option=com_projects&amp;task=cattitle.edit&amp;&id={$item->catalogID}&amp;return={$return}");
            $link = JHtml::link($url, $item->catalog);
            $arr['catalog'] = (!ProjectsHelper::canDo('projects.access.catalogs')) ? $item->catalog : $link;
            if ($item->hotel == null) $arr['square'] = sprintf("%s %s", $item->square, JText::sprintf('COM_PROJECTS_HEAD_ITEM_UNIT_SQM'));
            if ($item->hotel != null)
            {
                $arr['category'] = $item->category;
                $arr['hotel'] = $item->hotel;
            }
            $result[] = $arr;
        }
        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $cattitle = $this->getUserStateFromRequest($this->context . '.filter.cattitle', 'filter_cattitle');
        $this->setState('filter.cattitle', $cattitle);
        parent::populateState('number', 'asc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.cattitle');
        return parent::getStoreId($id);
    }
}
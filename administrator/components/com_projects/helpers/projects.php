<?php

use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\Model\AdminModel;

defined('_JEXEC') or die;

class ProjectsHelper
{
    public function addSubmenu($vName)
    {
        $view = JFactory::getApplication()->input->getString('view');
        $notify = self::getNotifies();
        if ($notify > 0) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_NOTIFY", $notify), "index.php?option=com_projects&amp;view=todos&amp;notify=1&amp;menu=sidebar&amp;from={$view}", $vName == "notify");
        }
        if (in_array($view, array("statv2", "reports", "contracts", "todos", "building", "stat", "scores", "payments", "catalogs", "cattitles", "prices", "sections", "items", "contracts_v2", "managerstat", "managertasks", ))) {
            JHtmlSidebar::addFilter(JText::sprintf("COM_PROJECTS_FILTER_SELECT_ACTIVE_PROJECT"), "set_active_project", JHtml::_("select.options", ProjectsHtmlFilters::projectOptions(), "value", "text", self::getActiveProject()));
        }
        if (self::canDo("projects.access.contracts.standart")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_CONTRACTS"), "index.php?option=com_projects&amp;view=contracts&amp;menu=sidebar&amp;from={$view}", $vName == "contracts");
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_CONTRACTS_V2"), "index.php?option=com_projects&amp;view=contracts_v2&amp;menu=sidebar&amp;from={$view}", $vName == "contracts_v2");
        }
        if (self::canDo("projects.access.exhibitors.standart")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_COMPANIES"), "index.php?option=com_projects&amp;view=exhibitors&amp;menu=sidebar&amp;from={$view}", $vName == "exhibitors");
        }
        if (self::canDo("projects.access.todos.standart")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_TODOS"), "index.php?option=com_projects&amp;view=todos&amp;menu=sidebar&amp;from={$view}", $vName == "todos");
        }
        if (self::canDo("projects.access.building")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_BUILDING"), "index.php?option=com_projects&amp;view=building&amp;menu=sidebar&amp;from={$view}", $vName == "building");
        }
        if (self::canDo("projects.access.templates.standart")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_TEMPLATES"), "index.php?option=com_projects&amp;view=templates&amp;menu=sidebar&amp;from={$view}", $vName == "templates");
        }
        if (self::canDo("projects.access.catalogs")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_CATALOG"), "index.php?option=com_projects&amp;view=catalogs&amp;menu=sidebar&amp;from={$view}", $vName == "catalogs");
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_CATTITLES"), "index.php?option=com_projects&amp;view=cattitles&amp;menu=sidebar&amp;from={$view}", $vName == "cattitles");
        }
        if (self::canDo("projects.access.prices")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_PRICES"), "index.php?option=com_projects&amp;view=prices&amp;menu=sidebar&amp;from={$view}", $vName == "prices");
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_SECTIONS"), "index.php?option=com_projects&amp;view=sections&amp;menu=sidebar&amp;from={$view}", $vName == "sections");
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_ITEMS"), "index.php?option=com_projects&amp;view=items&amp;menu=sidebar&amp;from={$view}", $vName == "items");
        }
        if (self::canDo("projects.access.projects")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS"), "index.php?option=com_projects&amp;view=projects&amp;menu=sidebar&amp;from={$view}", $vName == "projects");
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_PLANS"), "index.php?option=com_projects&amp;view=plans&amp;menu=sidebar&amp;from={$view}", $vName == "plans");
        }
        if (self::canDo("projects.access.finanses.standart")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_SCORES"), "index.php?option=com_projects&amp;view=scores&amp;menu=sidebar&amp;from={$view}", $vName == "scores");
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_PAYMENTS"), "index.php?option=com_projects&amp;view=payments&amp;menu=sidebar&amp;from={$view}", $vName == "payments");
        }
        if (self::canDo("projects.access.stat")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_STAT"), "index.php?option=com_projects&amp;view=statv2&amp;menu=sidebar&amp;from={$view}", $vName == "statv2");
        }
        if (self::canDo("projects.access.reports")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_REPORTS"), "index.php?option=com_projects&amp;view=reports&amp;menu=sidebar&amp;from={$view}", $vName == "reports");
        }
        /*if (self::canDo("projects.access.acts")) {
            JHtmlSidebar::addEntry(Text::_("COM_PROJECTS_MENU_ACTIVITIES"), "index.php?option=com_projects&amp;view=activities", $vName == "activities");
        }
        if (self::canDo("projects.access.rubrics")) {
            JHtmlSidebar::addEntry(Text::_("COM_PROJECTS_MENU_RUBRICS"), "index.php?option=com_projects&amp;view=rubrics", $vName == "rubrics");
        }*/
        if (self::canDo("projects.access.events.standart")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_EVENTS"), "index.php?option=com_projects&amp;view=events&amp;menu=sidebar&amp;from={$view}", $vName == "events");
        }
        if (self::canDo("projects.access.hotels.standart")) {
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_HOTELS"), "index.php?option=com_projects&amp;view=hotels&amp;menu=sidebar&amp;from={$view}", $vName == "hotels");
            JHtmlSidebar::addEntry(JText::sprintf("COM_PROJECTS_MENU_HOTEL_CATS"), "index.php?option=com_projects&amp;view=hotelcats&amp;menu=sidebar&amp;from={$view}", $vName == "hotelcats");
        }
    }

    public static function getCityTitle(int $cityID)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("c.name as city, r.name as region")
            ->from("#__grph_cities c")
            ->leftJoin("#__grph_regions r on r.id = c.region_id")
            ->where("c.id = {$cityID}");
        $item = $db->setQuery($query, 0, 1)->loadObject();
        return sprintf("%s (%s)", $item->city, $item->region);
    }

    /**
     * Возвращает массив настроек пользователя
     * @return array массив с настройками пользователя
     * @since 1.2.6.0
     */
    public static function getUserSettings(): array
    {
        $model = AdminModel::getInstance('Setting', 'ProjectsModel');
        $item = $model->getItem(array('userID' => JFactory::getUser()->id));
        return $item ?? array();
    }

    /**
     * Возвращает массив с ID сделок, к которым привязан пользователь
     * @param bool $advanced возвратить подробную информацию или нет
     * @return array массив с ID сделок
     * @since 1.2.5.0
     */
    public static function getUserContracts(bool $advanced = true): array
    {
        $id = (!JFactory::getUser()->guest) ? JFactory::getUser()->id : 0;
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        (!$advanced) ? $query->select("id") : $query->select("id, number, status, IFNULL(dat,'') as dat");
        $query->from("`#__prj_contracts`")
            ->where("`userID` = {$id}");
        if (!$advanced) return $db->setQuery($query)->loadColumn() ?? array(); else return $db->setQuery($query)->loadAssocList() ?? array();
    }

    /**
     * Возвращает ID сделки, к которому привязан юзер
     * @return int ID сделки
     * @since 1.2.5.0
     */
    public static function getUserContractID(): int
    {
        $session = JFactory::getSession();
        $id = $session->get('contractID', 0);
        if ($id > 0) return (int) $id;
        $ids = self::getUserContracts(false);
        if (count($ids) > 0) {
            self::setUserContract($ids[0]);
            return $ids[0];
        }
        else {
            return 0;
        }
    }

    /**
     * Устанавливает ID сделки, к которому привязан юзер
     * @param int $id ID сделки
     * @since 1.2.5.0
     */
    public static function setUserContract(int $id): void
    {
        $session = JFactory::getSession();
        $session->set('contractID', $id);
    }

    /**
     * Возвращает массив с ID пользователей, входящих в указанную группу
     * @param int $groupID ID группы пользователей
     * @return array
     * @since 1.2.0.7
     */
    public static function getUsersFromGroup(int $groupID = 0): array
    {
        if (empty($groupID)) return array();
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`user_id`")
            ->from("`#__user_usergroup_map`")
            ->where("`group_id` = {$groupID}");
        return $db->setQuery($query)->loadColumn() ?? array();
    }

    /**
     * Возвращает значение параметра из настроек текущего компонента
     * @param string $param Название параметра
     * @param string|null $default Значение по умолчанию
     * @param string $option Название компонента
     * @return mixed
     * @since 1.2.0.7
     */
    public static function getParam(string $param, string $default = null, string $option = 'com_projects')
    {
        $config = JComponentHelper::getParams($option);
        return $config->get($param, $default);
    }

    public static function addNotify(array $params): void
    {
        $db =& JFactory::getDbo();
        $data = new stdClass();
        $data->id = null;
        $data->is_notify = '1';
        $data->dat = JFactory::getDate()->format("Y-m-d");
        $data->contractID = $params['contractID'];
        $data->managerID = $params['managerID'];
        $data->task = $params['task'];
        $data->userOpen = JFactory::getUser()->id;
        $data->state = 0;
        $db->insertObject("#__prj_todos", $data);
    }

    /**
     * Возвращает следующий идентификатор группы уведомлений
     * @return int
     * @since 1.2.0.2
     */
    public static function getNotifyGroup(): int
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(MAX(`notify_group`),0)+1")
            ->from("`#__prj_todos`")
            ->where("`is_notify` = 1")
            ->where("`notify_group` is not null");
        return $db->setQuery($query)->loadResult() ?? 1;
    }

    /**
     * Возвращаем ассоциативный массив получателей уведомлений по изменениям в пунктах прайс-листа, если пункт прайс-листа == 0
     * Иначе возвращаем массив с ID юзеров
     * @param int $itemID ID пункта прайс-листа
     * @return array
     * @since 1.1.9.0
     */
    public static function getNotifyList(int $itemID = 0): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`managerID`")
            ->from("`#__prc_item_notifies`");
        if ($itemID == 0) {
            $query->select("`itemID`");
            $items = $db->setQuery($query)->loadAssocList() ?? array();
            $result = array();
            foreach ($items as $item) {
                if (!isset($result[$item['itemID']])) $result[$item['itemID']] = array();
                $result[$item['itemID']][] = $item['managerID'];
            }
            return $result;
        }
        else {
            $query->where("`itemID` = {$itemID}");
            $result = $db->setQuery($query)->loadColumn() ?? array();
            return $result;
        }
    }

    /**
     * Возвращает URL для обработки формы
     * @return string
     * @since 1.1.8.7
     * @throws
     */
    public static function getActionUrl(): string
    {
        $uri = JUri::getInstance();
        $query = $uri->getQuery();
        $client = (!JFactory::getApplication()->isClient('administrator')) ? 'site' : 'administrator';
        return JRoute::link($client, "index.php?{$query}");
    }

    /**
     * Возвращает текущий URL
     * @return string
     * @since 1.2.0.3
     * @throws
     */
    public static function getCurrentUrl(): string
    {
        $uri = JUri::getInstance();
        $query = $uri->getQuery();
        return "index.php?{$query}";
    }

    /**
     * Возвращает URL для возврата (текущий адрес страницы)
     * @return string
     * @since 1.1.8.7
     */
    public static function getReturnUrl(): string
    {
        $uri = JUri::getInstance();
        $query = $uri->getQuery();
        return base64_encode("index.php?{$query}");
    }

    /**
     * Возвращает URL для обработки формы левой панели
     * @return string
     * @since 1.2.0.3
     */
    public static function getSidebarAction():string
    {
        $return = self::getReturnUrl();
        return JRoute::_("index.php?return={$return}");
    }

    /**
     * Проверка необходимости обновить страницу
     * @since 1.2.0.3
     */
    public static function checkUpdate(): void
    {
        if (!empty($_POST)) {
            $location = self::getCurrentUrl();
            header("Location: {$location}");
        }
    }

    public static function searchExhibitor(int $id, string $text): string
    {
        $db =& JFactory::getDbo();
        $text = $db->q(urldecode($text));
        $query = $db->getQuery(true);
        $query
            ->select("`e`.`title_ru_short`, `e`.`title_ru_full`, `e`.`title_en`, `e`.`id`")
            ->select("`r`.`name` as `region`")
            ->select("`u`.`name` as `manager`")
            ->from("`#__prj_exp` as `e`")
            ->leftJoin("`#__grph_cities` as `r` on `r`.`id` = `e`.`regID`")
            ->leftJoin("`#__prj_contracts` as `c` on `c`.`expID` = `e`.`id`")
            ->leftJoin("`#__users` as `u` on `u`.`id` = `c`.`managerID`")
            ->where("(MATCH(`title_ru_short`,`title_ru_full`,`title_en`) AGAINST ({$text}))")
            ->where("`c`.`prjID` = 5");
        $items = $db->setQuery($query, 0, 5)->loadObjectList();
        $result = array();
        foreach ($items as $item) {
            $url = JRoute::_("index.php?option=com_projects&amp;task=search.asset&amp;old_id={$id}&amp;new_id={$item->id}");
            $result[] = sprintf("%s: %s (%s), %s", $item->region, JHtml::link($url, $item->title_ru_short), $item->title_ru_full, $item->manager);
        }
        return implode("<br>", $result);
    }

    /**
     * Возвращает массив с количеством просроченных заданий менеджеров
     * @param int $projectID ID проекта. Если 0 - по всем
     * @return array
     * @since 1.1.6.0
     */
    public static function getExpiredTodosByManager(int $projectID = 0): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`u`.`name` as `manager`, count(`t`.`id`) as `cnt`")
            ->from("`#__prj_todos` as `t`")
            ->leftJoin("`#__users` as `u` on `u`.`id` = `t`.`managerID`")
            ->where("`t`.`dat` < current_date")
            ->where("`t`.`state` = 0")
            ->where("`t`.`is_notify` = 0")
            ->group("`t`.`managerID`");
        if ($projectID > 0) {
            $query
                ->leftJoin("`#__prj_contracts` as `c` on `c`.`id` = `t`.`contractID`")
                ->where("`c`.`prjID` = {$projectID}");
        }
        $items = $db->setQuery($query)->loadObjectList();
        $result = array();
        if (empty($items)) return $result;
        foreach ($items as $item) {
            $result[$item->manager] = $item->cnt;
        }
        return $result;
    }

    /**
     * Возвращает массив с пунктами прайс-листа проекта
     * @param int $projectID ID проекта
     * @return array
     * @since 1.1.4.4
     */
    public static function getProjectPriceItems(int $projectID = 0): array
    {
        $result = array();
        if ($projectID == 0) return $result;
        $priceID = self::getProjectPrice($projectID);
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`i`.`id`, `i`.`title_ru`, `i`.`title_en`, `i`.`unit`, IFNULL(`i`.`unit_2`,'TWO_NOT_USE') as `unit_2`, `i`.`state`, `i`.`is_electric`, `i`.`is_internet`, `i`.`is_water`, `i`.`is_cleaning`, `i`.`is_multimedia`")
            ->select("`i`.`price_rub`, `i`.`price_usd`, `i`.`price_eur`")
            ->select("`i`.`column_1`, `i`.`column_2`, `i`.`column_3`")
            ->select("`p`.`title` as `price`, `s`.`title` as `section`")
            ->from('`#__prc_items` as `i`')
            ->leftJoin("`#__prc_sections` as `s` ON `s`.`id` = `i`.`sectionID`")
            ->leftJoin("`#__prc_prices` as `p` ON `p`.`id` = `s`.`priceID`")
            ->where("`s`.`priceID` = {$priceID}")
            ->order("`i`.`title_ru`");
        return $db->setQuery($query)->loadObjectList() ?? array();
    }

    /**
     * Возвращает массив со стендами из каталога проекта
     * @param int $projectID ID проекта
     * @return array
     * @since 1.1.4.4
     */
    public static function getProjectCatalogItems(int $projectID = 0): array
    {
        $result = array();
        if ($projectID == 0) return $result;
        $tip = ProjectsHelper::getProjectType($projectID);
        if ($tip > 0) return $result;
        $catalogID = self::getProjectCatalog($projectID);
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);

        $query
            ->select("`cat`.`id`, IFNULL(IFNULL(`cat`.`number`,`cat`.`title`),`cat`.`gos_number`) as `number`, `cat`.`square`")
            ->select("`e`.`id` as `exhibitorID`, `e`.`title_ru_short`, `e`.`title_ru_full`, `e`.`title_en`")
            ->from("`#__prj_catalog` as `cat`")
            ->leftJoin("`#__prj_stands` as `s` on `s`.`catalogID` = `cat`.`id`")
            ->leftJoin("`#__prj_contracts` as `c` on `c`.`id` = `s`.`contractID`")
            ->leftJoin("`#__prj_exp` as `e` on `e`.`id` = `c`.`expID`")
            ->where("`cat`.`titleID` = {$catalogID}");

        return $db->setQuery($query)->loadObjectList() ?? array();
    }

    /**
     * Возвращает массив сделок, которые имеют указанную рубрику
     * @param int $rubricID ID рубрики. 0 для получения всех сделок, у которых указана рубрика
     * @return array массив с ID сделок
     * @since 1.1.3.1
     */
    public static function getRubricContracts(int $rubricID = 0): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`contractID`")
            ->from("`#__prj_contract_rubrics`");
        if ($rubricID > 0) {
            $query->where("`rubricID` = {$rubricID}");
        }
        return $db->setQuery($query)->loadColumn() ?? array();
    }

    /**
     * Возвращает массив с ID рубрик сделки
     * @param int $contractID ID сделки
     * @param bool $title возвращать названия или нет
     * @return array массив с рубриками
     * @since 1.1.2.9
     */
    public static function getContractRubrics(int $contractID = 0, $title = false): array
    {
        if ($contractID == 0) return array();
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select((!$title) ? "`r`.`rubricID`" : "`t`.`title`")
            ->from("`#__prj_contract_rubrics` as `r`")
            ->where("`r`.`contractID` = {$contractID}");
        if ($title) {
            $query->leftJoin("`#__prj_rubrics` as `t` on `t`.`id` = `r`.`rubricID`");
        }
        return $db->setQuery($query)->loadColumn() ?? array();
    }

    /**
     * Возвращает массив с ID рубрик проекта
     * @param int $projectID ID проекта
     * @return array массив с рубриками
     * @since 1.1.3.0
     */
    public static function getProjectRubrics(int $projectID = 0): array
    {
        if ($projectID == 0) return array();
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`rubricID`")
            ->from("`#__prj_project_rubrics`")
            ->where("`projectID` = {$projectID}");
        return $db->setQuery($query)->loadColumn() ?? array();
    }

    /**
     * Возвращает массив с ID дочерних экспонентов
     * @param int $exhibitorID ID родительского экспонента
     * @param bool $title возвращать вместе с названием или нет
     * @return array массив с дочерними экспонентами
     * @since 1.1.2.8
     */
    public static function getExhibitorChildren(int $exhibitorID = 0, bool $title = false): array
    {
        if ($exhibitorID == 0) return array();
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`p`.`exhibitorID`")
            ->from("`#__prj_exp_parents` as `p`")
            ->where("`p`.`parentID` = {$exhibitorID}");
        if ($title) {
            $query
                ->select("`e`.`title_ru_short`, `e`.`title_ru_full`, `e`.`title_en`")
                ->leftJoin("`#__prj_exp` as `e` on `e`.`id` = `p`.`exhibitorID`");
        }
        if (!$title) {
            return $db->setQuery($query)->loadColumn() ?? array();
        }
        else {
            return $db->setQuery($query)->loadAssocList() ?? array();
        }
    }

    /**
     * Возвращает ID родителя экспонента
     * @param int $exhibitorID ID дочернего экспонента
     * @return int ID родителя или 0, если нет родителя
     * @since 1.1.2.8
     */
    public static function getExhibitorParent(int $exhibitorID = 0): int
    {
        if ($exhibitorID == 0) return 0;
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`parentID`")
            ->from("`#__prj_exp_parents`")
            ->where("`exhibitorID` = {$exhibitorID}");
        return $db->setQuery($query, 0, 1)->loadResult() ?? 0;
    }

    /**
     * Возвращает префиксы номеров договоров всех проектов
     * @return array массив номер проекта - префикс
     * @since 1.1.2.6
     */
    public static function getProjectsPrefix(): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`id`, `contract_prefix`")
            ->from("`#__prj_projects`");
        return $db->setQuery($query)->loadAssocList('id');
    }

    /**
     * Возвращает тип сделки - для стендов, делегаций и т.п.
     * @param int $contractID - ID сделки
     * @return int тип проекта. 0 - стенды, 1 - делегации
     * @since 1.1.3.6
     */
    public static function getContractType(int $contractID = 0): int
    {
        if ($contractID == 0) return -1;
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`tip`")
            ->from("`#__prj_contract_info`")
            ->where("`contractID` = {$contractID}");
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает тип проекта - для стендов, делегаций и т.п.
     * @param int $projectID - ID проекта
     * @return int тип проекта. 0 - стенды, 1 - делегации, 2 - транспорт
     * @since 1.1.3.6
     */
    public static function getProjectType(int $projectID = 0): int
    {
        if ($projectID == 0) return -1;
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`t`.`tip`")
            ->from("`#__prj_catalog_titles` as `t`")
            ->leftJoin("`#__prj_projects` as `p` on `p`.`catalogID` = `t`.`id`")
            ->where("`p`.`id` = {$projectID}");
        return $db->setQuery($query)->loadResult() ?? -1;
    }

    /**
     * Возвращает текстовое представление типа шаблона проекта
     * @param int $code числовой код типа проекта
     * @return string
     * @since 1.2.2.1
     */
    public static function getProjectTypeName(int $code = 0): string
    {
        switch ($code)
        {
            case 0:
                {
                    return 'stand';
                    break;
                }
            case 1:
                {
                    return 'number';
                    break;
                }
            case 2:
                {
                    return 'transport';
                    break;
                }
            default:
                {
                    return 'default';
                    break;
                }
        }
    }

    /**
     * Возвращает название раздела, где было действие пользователя
     * @param string $name
     * @return string
     * @since 1.1.1.8
     */
    public static function getEventSection(string $name): string
    {
        return JText::sprintf("COM_PROJECTS_HEAD_SECTION_EVENT_{$name}");
    }

    /**
     * Возвращает название действия, которое произвёл пользователь
     * @param string $name
     * @return string
     * @since 1.1.1.8
     */
    public static function getEventAction(string $name): string
    {
        return JText::sprintf("COM_PROJECTS_HEAD_ACTION_EVENT_{$name}");
    }

    /**
     * Добавляет запись в лог действий пользователя
     * @param array $data массив с событием
     * @since 1.1.1.3
     */
    public static function addEvent(array $data): void
    {
        $eventModel = AdminModel::getInstance('Event', 'ProjectsModel');
        $data['id'] = null;
        $eventModel->save($data);
    }

    /**
     * @param int $exhibitorID
     * @return array
     * @since 1.1.0.10
     */
    public static function getExhibitorActs(int $exhibitorID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`l`.`title`")
            ->from("`#__prj_exp_act` as `a`")
            ->leftJoin("`#__prj_activities` as `l` ON `l`.`id` = `a`.`actID`")
            ->where("`a`.`exbID` = {$exhibitorID}");
        return $db->setQuery($query)->loadColumn() ?? array();
    }

    /**
     * Возвращает массив с типами текущих отчётов
     * @return array
     * @since 1.1.0.6
     */
    public static function getReportTypes(): array
    {
        return array(
            'tasks_by_dates' => JText::sprintf('COM_PROJECTS_REPORT_TYPE_TASKS_BY_DATES'),
            'tasks_current_week' => JText::sprintf('COM_PROJECTS_REPORT_TYPE_TASKS_CURRENT_WEEK'),
            'exhibitors' => JText::sprintf('COM_PROJECTS_REPORT_TYPE_BY_EXHIBITORS'),
            //'managers' => JText::sprintf('COM_PROJECTS_REPORT_TYPE_BY_MANAGERS'),
            //'todos_by_dates' => JText::sprintf('COM_PROJECTS_REPORT_TYPE_TODOS_BY_DATES'),
            'squares' => JText::sprintf('COM_PROJECTS_REPORT_TYPE_SQUARES'),
            'pass' => JText::sprintf('COM_PROJECTS_REPORT_TYPE_PASS'),
        );
    }

    /**
     * Возвращает название текущего отчёта
     * @param string $type Тип отчёта
     * @return string
     * @since 1.1.0.6
     */
    public static function getReportType(string $type): string
    {
        $types = self::getReportTypes();
        return $types[$type];
    }

    /**
     * Возвращает текстовое представление адреса
     * @param array $data Массив с параметрами адреса в нужном порядке
     * @return string
     * @since 1.1.0.6
     */
    public static function buildAddress(array $data): string
    {
        foreach ($data as $i => $value) {
            if (empty($value)) unset($data[$i]);
        }
        return implode(", ", $data);
    }

    /**
     * Возвращает павильон, которому принадлежит стенд в соответствии с возвращённым из базы кодом
     * @param string $code код павильона
     * @return string
     * @since 1.1.0.4
     */
    public static function getStandPavilion(string $code): string
    {
        $tbl = array(0 => $code, 1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F');
        $pavilion = "";
        if (strlen($code) == 1 && is_numeric($code)) {
            $pavilion = JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_PAVILION_N', $tbl[intval($code)]);
        }
        if (strlen($code) == 1 && !is_numeric($code))
        {
            if ($code == 'V')
            {
                $pavilion = JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_CLOSE');
            }
            if ($code == 'Z')
            {
                $pavilion = JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_STREET_CLOSE');
            }
        }
        if (strlen($code) > 1)
        {
            $pavilion = JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_PAVILION_STREET', $tbl[intval(substr($code,1,1))]);
        }

        return $pavilion;
    }

    /**
     * Возвращает количество непрочитанных уведомлений
     * @since 1.0.9.3
     * @return int
     */
    public static function getNotifies(): int
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(COUNT(`id`),0)")
            ->from("`#__prj_todos`")
            ->where("`is_notify` = 1")
            ->where("`state` = 0");
        if (!self::canDo('projects.access.todos.full')) {
            $userID = JFactory::getUser()->id;
            $query->where("`managerID` = {$userID}");
        }
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает активный проект из глобальной настройки
     * @param string $default возвращаемое значение по умолчанию, если проект не выбран
     * @return mixed
     * @since 1.0.8.9
     */
    public static function getActiveProject(string $default = '')
    {
        $session = JFactory::getSession();
        $project = $session->get('active_project', '');
        return ($project != 0) ? $project : $default;
    }

    /**
     * Возвращает массив со списком ID сделок, у которых номер стенда указан вручную
     * @return array
     * @since 1.0.6.0
     * @deprecated
     * Не используется с 1.0.8.6
     */
    public static function getStandHandNumber(): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("DISTINCT `contractID`")
            ->from("`#__prj_stands`")
            ->where("`catalogID` IS NULL")
            ->where("`itemID` IS NULL");
        return $db->setQuery($query)->loadColumn() ?? array();
    }

    /**
     * Возвращает массив с площадями стендов по типам у указанной сделки
     * @param int $contractID ID сделки
     * @return array
     * @since 1.0.6.4
     */
    public static function getStandsSquare(int $contractID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`itemID`, SUM(`sq`) as `sq`")
            ->from("`#__prj_contract_stands`")
            ->group("`itemID`")
            ->where("`contractID` = {$contractID}");
        return $db->setQuery($query)->loadObjectList('itemID') ?? array();
    }

    /**
     * Загружает файл из формы в указанную директорию
     * @param string $field название поля из глобального массива $_FILES
     * @param string $folder наименование директории, куда загружать файл
     * @param int $id ID элемента
     * @return string имя загруженного файла
     * @since 1.0.4.2
     */
    public static function uploadFile(string $field, string $folder, int $id): string
    {
        $path = JPATH_ROOT . "/images/{$folder}/{$id}";
        $name = File::makeSafe($_FILES['jform']['name'][$field]);
        $tmp = $_FILES['jform']['tmp_name'][$field];
        Folder::create($path);
        chmod($path, 0777);
        if (!empty($tmp) && !empty($name)) {
            if (File::upload($tmp, $path . "/" . $name)) {
                chmod($path . "/" . $name, 0777);
            }
        }
        return $name;
    }

    /**
     * Возвращает сумму договоров по проекту
     * @param int $projectID ID проекта
     * @param array $statuses массив со статусами сделок, которые учитываются в договоре
     * @return array массив с 3 валютами
     * @since 1.0.4.3
     */
    public static function getProjectAmount(int $projectID, array $statuses = array(1, 2, 3, 4, 10)): array
    {
        $statuses = implode(', ', $statuses);
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(SUM(`a`.`price`),0) as `amount`, `a`.`currency`")
            ->from("`#__prj_contract_amounts` as `a`")
            ->leftJoin("`#__prj_contracts` as `c` ON `c`.`id` = `a`.`contractID`")
            ->where("`c`.`prjID` = {$projectID}")
            ->where("`c`.`status` IN ({$statuses})")
            ->group("`currency`");
        if (!ProjectsHelper::canDo('projects.access.contracts.full')) {
            $userID = JFactory::getUser()->id;
            $query->where("`c`.`managerID` = {$userID}");
        }
        $items = $db->setQuery($query)->loadObjectList();
        $result = array();
        foreach ($items as $item) {
            $result[$item->currency] = $item->amount;
        }
        return $result;
    }

    /**
     * Возвращает сумму всех платежей по проекту
     * @param int $projectID ID проекта
     * @param array $statuses массив со статусами сделок, которые учитываются в договоре
     * @return array сумма всех платежей в трёх валютах
     * @since 1.0.4.3
     */
    public static function getProjectPayments(int $projectID, array $statuses = array(1, 2, 3, 4, 10)): array
    {
        $statuses = implode(', ', $statuses);
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`p`.`payments`, `c`.`currency`")
            ->from("`#__prj_contract_payments` as `p`")
            ->leftJoin("`#__prj_contracts` as `c` ON `c`.`id` = `p`.`contractID`")
            ->where("`c`.`prjID` = {$projectID}")
            ->where("`c`.`status` IN ({$statuses})");
        if (!ProjectsHelper::canDo('projects.access.contracts.full')) {
            $userID = JFactory::getUser()->id;
            $query->where("`c`.`managerID` = {$userID}");
        }
        $payments = $db->setQuery($query)->loadObjectList();
        $result = array('rub' => 0, 'usd' => 0, 'eur' => 0);
        foreach ($payments as $payment) {
            $result[$payment->currency] += $payment->payments;
        }
        return $result;
    }

    /**
     * Возвращает сумму договора
     * @param int $contractID ID договора
     * @return float сумма договора
     * @since 1.0.4.0
     */
    public static function getContractAmount(int $contractID): float
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("sum(price) as price")
            ->from("`#__prj_stat_v2`")
            ->where("`contractID` = {$contractID}");

        return (float) 0 + $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает массив каталога со стендами
     * @param int $contractID ID сделки
     * @since 1.0.5.5
     * @return array массив со стендами
     */
    public static function getCatalogStands(int $contractID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`id` as `standID`, `number`, `square`-IFNULL((SELECT SUM(`value`) FROM `#__prj_contract_items` WHERE `contractID` = {$contractID} AND `catalogID` = `standID`),0) as `sq`")
            ->from("`#__prj_catalog`")
            ->order("`number`");
        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        foreach ($result as $item) {
            $title = sprintf("№%s (%s %s)", $item->number, $item->sq, JText::sprintf('COM_PROJECTS_HEAD_ITEM_UNIT_SQM'));
            $options[] = JHtml::_('select.option', $item->standID, $title);
        }

        return $options;
    }

    /**
     * Возвращает сумму вместе с валютой
     * @param float $amount Сумма
     * @param string $currency Валюта
     * @return string
     * @since 1.0.8.6
     */
    public static function getCurrency(float $amount, string $currency): string
    {
        return sprintf("%s %s", number_format($amount, '2', ',', ' '), JText::sprintf("COM_PROJECTS_HEAD_ITEM_PRICE_SMALL_" . mb_strtoupper($currency)));
    }

    /**
     * Возвращает валюту сделки
     * @param int $contractID ID сделки
     * @return string
     * @since 1.0.4.0
     */
    public function getContractCurrency(int $contractID): string
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`currency`")
            ->from("`#__prj_contracts`")
            ->where("`id` = {$contractID}");
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает ID проекта, на который заключена сделка
     * @param int $contractID ID сделки
     * @return int ID проекта
     * #@since 1.0.4.5
     */
    public static function getContractProject(int $contractID): int
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`prjID`")
            ->from("`#__prj_contracts`")
            ->where("`id` = {$contractID}");
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает сделки, которые участвуют в проекте
     * @param int $projectID ID проекта
     * @return array массив со сделками
     * @since 1.0.3.5
     * @deprecated 1.1.4.5
     */
    public static function getProjectContracts(int $projectID): array
    {
        $result = array();
        if ($projectID == 0) return $result;
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`c`.`id` as `contractID`, `c`.`number`, `c`.`status`")
            ->select("`e`.`title_ru_full`, `e`.`title_ru_short`, `e`.`title_en`, `e`.`id` as `exponentID`")
            ->select("`u`.`name` as `manager`, (SELECT COUNT(*) FROM `#__prj_todos` WHERE `contractID`=`c`.`id` AND `state`=0) as `plan_cnt`")
            ->from("`#__prj_contracts` as `c`")
            ->leftJoin("`#__prj_exp` as `e` ON `e`.`id` = `expID`")
            ->leftJoin("`#__users` as `u` ON `u`.`id` = `c`.`managerID`")
            ->where("`c`.`prjID` = {$projectID}")
            ->order("`plan_cnt` DESC");
        $items = $db->setQuery($query)->loadObjectList();
        $return = base64_encode(JUri::base() . "index.php?option=com_projects&view=project&layout=edit&id={$projectID}");
        foreach ($items as $item) {
            $arr = array();
            $url = JRoute::_("index.php?option=com_projects&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$return}");
            $contract = self::getExpStatus($item->status);
            if (!empty($item->number)) $contract = sprintf("%s №%s", $contract, $item->number);
            $arr['contract'] = JHtml::link($url, $contract);
            $arr['manager'] = $item->manager;
            $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$item->exponentID}&amp;return={$return}");
            $exhibitor = self::getExpTitle($item->title_ru_short, $item->title_ru_full, $item->title_en);
            $arr['exhibitor'] = JHtml::link($url, $exhibitor);
            $arr['plan_cnt'] = $item->plan_cnt;
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * Возвращает массив, ключ - ID счёта, значение - сумма платежей по нему
     * @param array $scoreIDs Массив с ID сделок
     * @return array
     * @since 1.3.0.8
     */
    public static function getPaymentSum(array $scoreIDs): array
    {
        $result = array();
        if (empty($scoreIDs)) return $result;
        $ids = implode(', ', $scoreIDs);
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`id`, `scoreID`, `amount`")
            ->from("`#__prj_payments`")
            ->where("`scoreID` IN ({$ids})");
        $payments = $db->setQuery($query)->loadObjectList();
        foreach ($payments as $payment) {
            if ($result[$payment->scoreID]) $result[$payment->scoreID] = (float)0;
            $result[$payment->scoreID] += $payment->amount;
        }
        return $result;
    }

    /**
     * Возвращает активную колонку прайса для указанной сделки
     * @param int $contractID ID контракта
     * @return int ID колонки
     * @since 1.2.9.5
     */
    public static function getActivePriceColumn(int $contractID): int
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(`p`.`columnID`,1) as `column`")
            ->from("`#__prj_contracts` as `c`")
            ->leftJoin("`#__prj_projects` as `p` ON `p`.`id` = `c`.`prjID`")
            ->where("`c`.`id` = {$contractID}");
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает ID каталога со стендами для указанного проекта
     * @param int $projectID
     * @return int
     * @since 1.0.7.1
     */
    public static function getProjectCatalog(int $projectID): int
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`catalogID`")
            ->from("`#__prj_projects`")
            ->where("`id` = {$projectID}");
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает ID прайс-листа для указанного проекта
     * @param int $projectID
     * @return int
     * @since 1.0.5.2
     */
    public static function getProjectPrice(int $projectID): int
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(`priceID`,0)")
            ->from("`#__prj_projects`")
            ->where("`id` = {$projectID}");
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает текстовый статус счёта
     * @param int $state
     * @return string
     * @since 1.2.3
     */
    public static function getScoreState(int $state): string
    {
        return JText::sprintf("COM_PROJECTS_HEAD_SCORE_STATE_{$state}");
    }

    /**
     * Возвращает тип шаблона
     * @param int $tip
     * @return string
     * @since 1.1.0.1
     */
    public static function getTemplateType(int $tip = 0): string
    {
        return JText::sprintf("COM_PROJECTS_HEAD_TMPL_TYPE_{$tip}");
    }

    /**     * Возвращает текстовый статус задачи из планировщика
     * @param int $state
     * @return string
     * @since 1.2.3
     */
    public static function getTodoState(int $state): string
    {
        return JText::sprintf("COM_PROJECTS_HEAD_TODO_STATE_{$state}");
    }

    /**
     * Возвращает дополнительную информацию о планируемом платеже
     * @param int $scoreID ID счёта
     * @return object
     * @since 1.3.0.8
     */
    public static function getPaymentAdvInfo(int $scoreID): object
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`s`.`number` as `score`, `c`.`number` as `contract`")
            ->select("`e`.`title_ru_full`, `e`.`title_ru_short`, `e`.`title_en`")
            ->from("`#__prj_scores` as `s`")
            ->leftJoin("`#__prj_contracts` as `c` ON `c`.`id` = `s`.`contractID`")
            ->leftJoin("`#__prj_exp` as `e` ON `e`.`id` = `c`.`expID`")
            ->where("`s`.`id` = {$scoreID}");
        return $db->setQuery($query)->loadObject();
    }

    /**
     * Возвращает следующий по очереди номер договора
     * @param int $projectID ID сделки
     * @return int
     * @since 1.0.9.8
     */
    public static function getContractNumber(int $projectID): int
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(MAX(`number`)+1,1)")
            ->from("`#__prj_contracts`")
            ->where("`prjID` = {$projectID}")
            ->where("`number` IS NOT NULL");
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает счета текущей сделки
     * @param int $contractID
     * @return array
     * @since 1.0.3.6
     */
    public static function getContractScores(int $contractID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`id`, DATE_FORMAT(`dat`,'%d.%m.%Y') as `dat`, `number`, `amount`, `state`")
            ->from("`#__prj_scores`")
            ->where("`contractID` = {$contractID}");
        return $db->setQuery($query)->loadObjectList() ?? array();
    }

    /**
     * Возвращает платежи по счетам для текущей сделки
     * @param int $contractID
     * @return array
     * @since 1.0.3.6
     */
    public static function getContractPayments(int $contractID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("DATE_FORMAT(`p`.`dat`,'%d.%m.%Y') as `dat`, `p`.`scoreID`, `p`.`pp`, `p`.`amount`")
            ->from("`#__prj_payments` as `p`")
            ->leftJoin("`#__prj_scores` as `s` ON `s`.`id` = `p`.`scoreID`")
            ->where("`s`.`contractID` = {$contractID}");
        $items = $db->setQuery($query)->loadObjectList();
        $result = array();
        foreach ($items as $item) {

            if (!isset($result[$item->scoreID])) $result[$item->scoreID] = array();
            $result[$item->scoreID][] = $item;
        }
        return $result;
    }

    /**
     * Возвращает статус сделки
     * @param int $status код статусв сделки
     * @param int $number номер договора
     * @param string $date дата заключения договора
     * @param string $exhibitor название экспонента
     * @param bool $coExp является ли соэкспонентом
     * @return string
     * @since 1.1.1.2
     */
    public static function getContractTitle(int $status, int $number = 0, string $date = '', string $exhibitor = '', bool $coExp = false): string
    {
        if ($status != 1) {
            $text = self::getExpStatus($status);
        }
        else {
            if ($number != 0) {
                $date = JDate::getInstance($date)->format("d.m.Y");
                if ($exhibitor != '') {
                    $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITH_DATE_AND_EXHIBITOR', $number, $date, $exhibitor);
                }
                else {
                    $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITH_DATE', $number, $date);
                }
            }
            else {
                if ($exhibitor != '') {
                    $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER_WITH_EXHIBITOR', $exhibitor);
                }
                else {
                    $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER');
                }
            }
        }
        if ($coExp) $text .= " - " . JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_5');
        return $text;
    }

    /**
     * Возвращает текст для отображения в поле типа "Сделка"
     * @param int $status код статусв сделки
     * @param string $number номер договора
     * @param string $date дата заключения договора
     * @param string $exhibitor название экспонента
     * @param string $project название проекта
     * @return string
     * @since 1.1.1.2
     */
    public static function getContractFieldTitle(int $status, string $number = '0', string $date = '', string $exhibitor = '', string $project = ''): string
    {
        if ($status != 1) {
            $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT', $exhibitor, $project);
            $text = sprintf("%s - %s",$text, self::getExpStatus($status));
        }
        else {
            if ($number != 0) {
                $date = JDate::getInstance($date)->format("d.m.Y");
                if ($exhibitor != '') {
                    $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITH_DATE_AND_EXHIBITOR', $number, $date, $exhibitor);
                }
                else {
                    $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITH_DATE', $number, $date);
                }
            }
            else {
                if ($exhibitor != '') {
                    $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER_WITH_EXHIBITOR', $exhibitor);
                }
                else {
                    $text = JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER');
                }
            }
        }
        return $text;
    }

    /**
     * Возвращает список стендов из указанной сделки
     * @param int $contractID
     * @return array
     * @since 1.1.2.2
     */
    public static function getContractStands(int $contractID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`s`.`id`, `s`.`tip`, `c`.`square` as `sq`, `s`.`freeze`, `s`.`comment`, `s`.`status`, `s`.`scheme`, `s`.`itemID`, `s`.`contractID`, `s`.`arrival`, `s`.`department`")
            ->select("IFNULL(`c`.`number`,`c`.`title`) as `number`, `c`.`title`")
            ->select("`cats`.`title_ru` as `category`, `h`.`title_ru` as `hotel`")
            ->select("`i`.`title_ru` as `item`")
            ->from("`#__prj_stands` as `s`")
            ->leftJoin("`#__prj_catalog` as `c` ON `c`.`id` = `s`.`catalogID`")
            ->leftJoin("`#__prj_hotels_number_categories` as `cats` ON `cats`.`id` = `c`.`categoryID`")
            ->leftJoin("`#__prj_hotels` as `h` ON `h`.`id` = `cats`.`hotelID`")
            ->leftJoin("`#__prc_items` as `i` ON `i`.`id` = `s`.`itemID`")
            ->where("`s`.`contractID` = {$contractID}");
        return array_merge($db->setQuery($query)->loadObjectList(), self::loadDelegatedStands($contractID));
    }

    /**
     * Возвращает список делегированных стендов
     * @param int $contractID
     * @return array
     * @since 1.1.2.3
     */
    function loadDelegatedStands(int $contractID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`s`.`id`, `s`.`tip`, `c`.`square` as `sq`, `s`.`freeze`, `s`.`comment`, `s`.`status`, `s`.`scheme`, `s`.`itemID`, `s`.`contractID`")
            ->select("CONCAT(`c`.`number`,' *') as `number`")
            ->select("`i`.`title_ru` as `item`")
            ->from("`#__prj_stands_delegate` as `d`")
            ->leftJoin("`#__prj_stands` as `s` ON `s`.`id` = `d`.`standID`")
            ->leftJoin("`#__prj_catalog` as `c` ON `c`.`id` = `s`.`catalogID`")
            ->leftJoin("`#__prc_items` as `i` ON `i`.`id` = `s`.`itemID`")
            ->where("`d`.`contractID` = {$contractID}");
        return $db->setQuery($query)->loadObjectList();
    }

    /**
     * Возвращает массив с контактными лицами для указанного экспонента
     * @param int $exbID ID экспонента
     * @return array
     * @since 1.3.0.9
     */
    public static function getExhibitorPersons(int $exbID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("*")
            ->from("`#__prj_exp_persons` as `p`")
            ->where("`p`.`exbID` = {$exbID}");
        return $db->setQuery($query)->loadObjectList();
    }

    /**
     * Возвращает список заданий из планировщика для указанной сделки
     * @param int $contractID
     * @return array
     * @since 1.2.9.7
     */
    public static function getContractTodo(int $contractID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`t`.`id`, DATE_FORMAT(`t`.`dat`,'%d.%m.%Y') as `dat`, `u`.`name` as `user`, `t`.`task`, `t`.`result`, `t`.`state`")
            ->select("IF(`t`.`dat`<CURRENT_DATE(),IF(`t`.`state`=0,'1','0'),'0') as `expired`")
            ->from("`#__prj_todos` as `t`")
            ->leftJoin("`#__users` as `u` ON `u`.`id` = IF(`t`.`state`!=0,`t`.`userClose`,`t`.`managerID`)")
            ->where("`t`.`contractID` = {$contractID}")
            ->where("`t`.`is_notify` != 1")
            ->order("`t`.`state` asc, `t`.`dat` desc");
        return $db->setQuery($query)->loadObjectList();
    }

    /**
     * Проверяет возможность использовать указанный номер в договоре
     * @param   int $number Номер договора
     * @return boolean
     * @since 1.2.2
     */
    public static function checkContractNumber(int $number): bool
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("COUNT(`id`)")
            ->from("`#__prj_contracts`")
            ->where("`number` = {$number}");
        return ($db->setQuery($query)->loadResult() != 0) ? false : true;
    }

    /**
     * Возвращает ID экспонентов по указанному виду деятельности
     * @param int $id ID вида деятальности
     * @return array
     * @since 1.2.0
     */
    public static function getExponentsInActivities(int $id): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('`exbID`')
            ->from("`#__prj_exp_act`")
            ->where("`actID` = {$id}");
        $items = $db->setQuery($query)->loadObjectList();
        $result = array();
        if (!empty($items)) {
            foreach ($items as $item) {
                $result[] = $item->exbID;
            }
        }
        return $result;
    }

    /**
     * Возвращает название экспонента в зависимости от заполненных данных.
     * @param string|null $title_ru_short Короткое название по-русски.
     * @param string|null $title_ru_full Полное название по-русски.
     * @param string|null $title_en Название по-английски.
     * @return string
     * @since 1.1.9
     */
    public static function getExpTitle($title_ru_short = null, $title_ru_full = null, $title_en = null): string
    {
        $title = $title_ru_short ?? $title_ru_full ?? $title_en;
        return $title ?? JText::sprintf('COM_PROJECTS_ERROR_NOT_GET_EXP_TITLE');
    }

    /**
     * Возвращает тип каталога - стенды или номера
     * @param int $tip
     * @return string
     * @since 1.1.0.11
     */
    public static function getCatalogType(int $tip): string
    {
        return JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_CATALOG_TYPE_{$tip}");
    }

    /**
     * Возвращает список сделок, у которых $exhibitorID является родителем по проекту $projectID
     * @param int $exhibitorID ID родителя
     * @param int $projectID ID проекта
     * @return array
     * @since 1.1.0.12
     */
    public static function getContractCoExp(int $exhibitorID, int $projectID): array
    {
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`id`")
            ->from("`#__prj_contracts`")
            ->where("`parentID` = {$exhibitorID}")
            ->where("`prjID` = {$projectID}")
            ->where("`isCoExp` = 1");
        return $db->setQuery($query)->loadColumn() ?? array();
    }

    /**
     * Возвращает статус участия экспонента в проекте.
     * @param string|null $status Значение из таблицы.
     * @param int $isCoExp Является соэкспонентом
     * @return string
     * @since 1.1.0.6
     */
    public static function getExpStatus($status, $isCoExp = 0): string
    {
        if ($status === null) return JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_UNDEFINED');
        $status = mb_strtoupper($status);
        if ($isCoExp != 0 && $status == '0') {
            return JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_5');
        }
        return JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_{$status}");
    }

    public static function getApplication(string $app): string
    {
        $app = strtoupper($app);
        return JText::sprintf("COM_PROJECTS_HEAD_ITEM_APPLICATION_{$app}");
    }

    /**
     * @param int $id ID типа стенда
     * @return string
     * @since 1.3.0.2
     */
    public static function getStandType(int $id): string
    {
        return JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STAND_TYPE_{$id}");
    }

    /**
     * @param int $id ID типа стенда
     * @return string
     * @since 1.3.0.2
     */
    public static function getStandStatus(int $id): string
    {
        return JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STAND_STATUS_{$id}");
    }

    /**
     * Возвращает русское значение единицы измерения пункта прайса.
     * @param string $name Значение из таблицы.
     * @return string
     * @since 1.1.4
     */
    public static function getUnit(string $name): string
    {
        $name = mb_strtoupper($name);
        try {
            $text = JText::sprintf("COM_PROJECTS_HEAD_ITEM_UNIT_{$name}");
        } catch (Exception $exception) {
            $text = JText::sprintf('COM_PROJECTS_HEAD_ITEM_UNIT_UNKNOWN');
        }
        return $text;
    }

    /**
     * Возвращает права доступа текущего пользователя
     * @param string $action требуемое право для проверки доступа
     * @return bool
     * @since 1.2.5
     */
    public static function canDo(string $action): bool
    {
        return JFactory::getUser()->authorise($action, 'com_projects');
    }
}

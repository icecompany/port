<?php
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

defined('_JEXEC') or die;

class ProjectsModelApi extends BaseDatabaseModel
{
    public function __construct($config = array())
    {
        $this->api_key = JFactory::getApplication()->input->getString('api_key', '');
        if (!$this->checkKey() || $this->api_key == '') exit();
        parent::__construct($config);
    }

    /**
     * Возвращает соль на текущий день
     * @return int
     * @since 1.2.0.0
     */
    public function getSalt(): int
    {
        $config = JComponentHelper::getParams('com_projects');
        return $config->get('aes_key', 0);
    }

    /**
     * Возвращаает список всех экспонентов
     * @return array
     * @since 1.2.0.0
     * @throws
     */
    public function getExhibitors(): array
    {
        $db =& JFactory::getDbo();
        $name = JFactory::getApplication()->input->getString('q', '');
        $q = $db->q("%{$name}%");
        $query = $db->getQuery(true);
        $query
            ->select("*")
            ->from("`#__prj_exhibitors_all`");
        if (!empty($name)) {
            $query = $db->getQuery(true);
            $query
                ->select("IFNULL(`title_ru_short`,`title_ru_full`) as exhibitor")
                ->from("`#__prj_exp`")
                ->where("(`title_ru_short` LIKE {$q} OR `title_ru_full` LIKE {$q} OR `title_en` LIKE {$q})");
        }
        return $db->setQuery($query)->loadObjectList() ?? array();
    }

    public function getCities(): array
    {
        $db =& JFactory::getDbo();
        $q = JFactory::getApplication()->input->getString('q', '');
        $q = $db->q("%{$q}%");
        $query = $db->getQuery(true);
        $query
            ->select("`c`.`id`, `c`.`name` as `city`, `r`.`name` as `region`, `s`.`name` as `country`")
            ->from('`#__grph_cities` as `c`')
            ->leftJoin('`#__grph_regions` as `r` ON `r`.`id` = `c`.`region_id`')
            ->leftJoin('`#__grph_countries` as `s` ON `s`.`id` = `r`.`country_id`')
            ->order("`c`.`is_capital` DESC, `c`.`name`")
            ->where("`s`.`state` = 1")
            ->where("c.name like {$q}");
        $result = $db->setQuery($query)->loadObjectList();
        $options = array();
        if (count($result) > 0) {
            foreach ($result as $p) {
                $reg = sprintf("%s, %s", $p->region, $p->country);
                $options[] = array('id' => $p->id, 'name' => $p->city, 'region' => $reg);
            }
        }
        return $options;
    }

    /**
     * Регистрация компании в системе
     * @throws Exception
     * @since 1.2.0.0
     */
    public function registerUser(): int
    {
        $id = JFactory::getApplication()->input->getInt('id', 0);
        $email = JFactory::getApplication()->input->getString('email', '');
        if ($email == '' || $id == 0) return 0;
        $data['username'] = $email;
        $data['name'] = $this->getCompanyName($id);
        $data['email'] = $email;
        $data['password'] = $this->getPasswordFromUrl();
        $data['groups'] = array(2);
        $user = new JUser;
        $user->bind($data);
        $user->save();
        $uid = $user->id;
        $this->updateExhibitorUserId($id, $uid);
        return $uid;
    }

    /**
     * Возвращает название компании
     * @param int $exhibitorID ID компании
     * @return string Название компании
     * @since 1.2.0.1
     */
    private function getCompanyName(int $exhibitorID = 0): string
    {
        if ($exhibitorID == 0) return '';
        $db =& JFactory::getDbo();
        $id = $db->q($exhibitorID);
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(`title_ru_short`,ifnull(`title_ru_full`,ifnull(`title_en`,'Компания без названия')))")
            ->from("`#__prj_exp`")
            ->where("`id` = {$id}");
        return $db->setQuery($query)->loadResult() ?? 'Компания без имени';
    }

    /**
     * Привязывает ID учётной записи к ID компании
     * @param int $exhibitorID ID компании
     * @param int $userID ID учётной записи юзера
     * @since 1.2.0.0
     */
    private function updateExhibitorUserId(int $exhibitorID = 0, int $userID = 0): void
    {
        if ($exhibitorID == 0 || $userID == 0) return;
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->update("`#__prj_exp`")
            ->set("`user_id` = {$userID}")
            ->where("`id` = {$exhibitorID}");
        $db->setQuery($query, 0, 1)->execute();
    }

    /**
     * Возвращает дешифрованный пароль из адресной строки
     * @return string
     * @throws Exception
     * @since 1.2.0.0
     */
    private function getPasswordFromUrl(): string
    {
        $password = JFactory::getApplication()->input->getString('password', '');
        if ($password == '') return '';
        $aes = $this->getSalt();
        $db =& JFactory::getDbo();
        $password = $db->q(base64_decode($password));
        $query = $db->getQuery(true);
        $query
            ->select("decode({$password}, {$aes})")
            ->from("`#__extensions`")
            ->where("`element` like 'com_projects'");
        return $db->setQuery($query)->loadResult() ?? '';
    }

    /**
     * Проверка ключа доступа к API
     * @return bool
     * @since 1.2.0.0
     */
    private function checkKey(): bool
    {
        $db =& JFactory::getDbo();
        $k = $db->q($this->api_key);
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(`id`,0)")
            ->from("`#__api_keys`")
            ->where("`api_key` LIKE {$k}");
        $result = (int) $db->setQuery($query)->loadResult() ?? 0;
        return ($result == 0) ? false : true;
    }

    private $api_key;
}

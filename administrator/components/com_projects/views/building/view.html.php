<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ProjectsViewBuilding extends HtmlView
{
	protected $helper;
	protected $sidebar = '';
	public $items, $pagination, $uid, $state, $links, $filterForm, $activeFilters, $_layout, $advanced_items, $userSettings;

	public function display($tpl = null)
	{
	    $this->items = $this->get('Items');
	    $this->pagination = $this->get('Pagination');
	    $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->_layout = $this->get('Layout');
        $this->userSettings = ProjectsHelper::getUserSettings();
        if ($this->_layout != '') {
            $this->filterForm->removeField('manager', 'filter');
            $this->filterForm->removeField('standtype', 'filter');
            $this->filterForm->removeField('standstatus', 'filter');
            $this->filterForm->setFieldAttribute('search', 'hint', JText::sprintf('COM_PROJECTS_HEAD_NUMBER_TITLE'), 'filter');
        }
        if ($this->_layout == '') {
            $this->filterForm->removeField('hotel', 'filter');
            $this->filterForm->removeField('arrival', 'filter');
            $this->filterForm->removeField('department', 'filter');
            $this->advanced_items = array_keys($this->items['advanced']);
        }
        $this->filterForm->setValue('limit', 'list', $this->state->get('list.limit'));

		// Show the toolbar
		$this->toolbar();

		// Show the sidebar
		$this->helper = new ProjectsHelper();
		$this->helper->addSubmenu('building');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		return parent::display($tpl);
	}

	private function toolbar()
	{
		JToolBarHelper::title(Text::_('COM_PROJECTS_MENU_BUILDING'), '');
        JToolbarHelper::custom('settings.building', 'options', '', 'COM_PROJECTS_MENU_SETTING_VISIBILITY', false);
		if (ProjectsHelper::canDo('core.admin'))
		{
			JToolBarHelper::preferences('com_projects');
		}
	}
}

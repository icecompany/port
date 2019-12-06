<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ProjectsViewReports extends HtmlView
{
	protected $helper;
	protected $sidebar = '';
	public $items, $pagination, $uid, $state, $links, $isAdmin, $filterForm, $activeFilters, $type, $fields, $notAvailableFilters, $itemIDs, $dat;

	public function display($tpl = null)
	{
        $this->type = $this->get('Type');
        $this->items = (!empty($this->type)) ? $this->get('Items') : array();
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->notAvailableFilters = $this->get('NotAvailableFilters');
        $this->itemIDs = $this->get('ItemIDs');
        $this->dat = $this->get('Dat');
        $this->fields = $this->state->get('filter.fields', array());

        if ($this->type === 'tasks_by_dates' || $this->type === 'tasks_current_week') {
            $this->filterForm->setValue('dat', 'filter', $this->state->get('filter.dat'));
            if ($this->type === 'tasks_by_dates') $this->filterForm->setValue('dynamic', 'filter', $this->state->get('filter.dynamic'));
        }

        /* Удаляем поля, по которым не нужен фильтр, в зависимости от типа отчёта */
        foreach ($this->notAvailableFilters as $filter) {
            $this->filterForm->removeField($filter, 'filter');
        }
		// Show the toolbar
		$this->toolbar();

		// Show the sidebar
		$this->helper = new ProjectsHelper();
		$this->helper->addSubmenu('reports');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		return parent::display($tpl);
	}

	private function toolbar()
	{
	    $title = (!empty($this->type)) ? ProjectsHelper::getReportType($this->type) : JText::sprintf('COM_PROJECTS_MENU_REPORTS_DESC');
		JToolBarHelper::title($title, '');
		if (!empty($this->type))
        {
            JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_projects&view=reports');
        }
        if ($this->type === 'tasks_by_dates') {
            JToolbarHelper::custom('reports.refresh', 'refresh', 'refresh', JText::sprintf('COM_PROJECT_TASK_REFRESH'), false);
        }
        if (ProjectsHelper::canDo('core.admin'))
        {
            JToolBarHelper::preferences('com_projects');
        }
    }
}

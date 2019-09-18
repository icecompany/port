create unique index `#__prj_manager_contracts_history_dmp_uindex`
    on `#__prj_manager_contracts_history` (dat, managerID, projectID);

alter table `#__prj_manager_contracts_history`
    add sync_time timestamp default current_timestamp not null;

alter table `#__prj_manager_contracts_history` modify exhibitors int(11) unsigned default 0 not null comment 'Количество компаний в работе';

alter table `#__prj_manager_contracts_history` modify plan int(11) unsigned default 0 not null comment 'Количество незавершённых задач';

rename table `#__prj_manager_contracts_history` to `#__prj_managers_stat`;

alter table `#__prj_managers_stat` comment 'Статистика работы менеджеров';

alter table `#__prj_managers_stat`
    add todos_expires int unsigned default 0 not null comment 'Просроченные задачи' after plan;

alter table `#__prj_managers_stat`
    add todos_plan int unsigned default 0 not null comment 'Задачи на этот день' after todos_expires;

alter table `#__prj_managers_stat`
    add todos_future int unsigned default 0 not null comment 'Будущие задачи' after todos_plan;

alter table `#__prj_managers_stat`
    add todos_completed int unsigned default 0 not null comment 'Задач закрыто' after todos_future;


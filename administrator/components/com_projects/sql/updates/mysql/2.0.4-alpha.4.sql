create table `#__prj_manager_tasks_by_dates`
(
    id int unsigned auto_increment,
    control_date date not null comment 'Дата снимка',
    dat date not null comment 'Дата задачи',
    projectID int not null,
    managerID int not null,
    expires int default 0 not null comment 'Просроченные',
    `current` int default 0 not null comment 'Текущие на сутки',
    future int default 0 not null comment 'Будущие',
    constraint `#__prj_manager_tasks_by_dates_pk`
        primary key (id),
    constraint `#__prj_manager_tasks_by_dates_#__prj_projects_id_fk`
        foreign key (projectID) references `#__prj_projects` (id)
            on update cascade on delete cascade,
    constraint `#__prj_manager_tasks_by_dates_#__users_id_fk`
        foreign key (managerID) references `#__users` (id)
            on update cascade on delete cascade
)
    comment 'Статистика задач менеджеров по датам';

create unique index `#__prj_manager_tasks_by_dates_dat_projectID_managerID_uindex`
    on `#__prj_manager_tasks_by_dates` (dat, projectID, managerID);


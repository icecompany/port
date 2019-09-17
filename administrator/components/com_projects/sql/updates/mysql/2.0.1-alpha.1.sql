create table `#__prj_manager_contracts_history`
(
    id        int auto_increment,
    managerID int                       not null comment 'ID менеджера',
    dat       date         default null null,
    status_0  int unsigned default 0    null,
    status_1  int unsigned default 0    not null,
    status_2  int unsigned default 0    not null,
    status_3  int unsigned default 0    not null,
    status_4  int unsigned default 0    not null,
    status_7  int unsigned default 0    not null,
    status_8  int unsigned default 0    not null,
    status_9  int unsigned default 0    not null,
    status_10 int unsigned default 0    not null,
    constraint `#__prj_manager_contracts_history_pk`
        primary key (id),
    constraint `#__prj_manager_contracts_history_#__users_id_fk`
        foreign key (managerID) references `#__users` (id)
)
    comment 'История статусов сделок менеджеров';

create index `#__prj_manager_contracts_history_dat_index`
    on `#__prj_manager_contracts_history` (dat);

create index `#__prj_manager_contracts_history_status_0_index`
    on `#__prj_manager_contracts_history` (status_0);

create index `#__prj_manager_contracts_history_status_10_index`
    on `#__prj_manager_contracts_history` (status_10);

create index `#__prj_manager_contracts_history_status_1_index`
    on `#__prj_manager_contracts_history` (status_1);

create index `#__prj_manager_contracts_history_status_2_index`
    on `#__prj_manager_contracts_history` (status_2);

create index `#__prj_manager_contracts_history_status_3_index`
    on `#__prj_manager_contracts_history` (status_3);

create index `#__prj_manager_contracts_history_status_4_index`
    on `#__prj_manager_contracts_history` (status_4);

create index `#__prj_manager_contracts_history_status_7_index`
    on `#__prj_manager_contracts_history` (status_7);

create index `#__prj_manager_contracts_history_status_8_index`
    on `#__prj_manager_contracts_history` (status_8);

create index `#__prj_manager_contracts_history_status_9_index`
    on `#__prj_manager_contracts_history` (status_9);

alter table `#__prj_manager_contracts_history`
    modify dat date null after status_10;

alter table `#__prj_manager_contracts_history`
    add projectID int not null after managerID;

alter table `#__prj_manager_contracts_history`
    add constraint `#__prj_manager_contracts_history_#__prj_projects_id_fk`
        foreign key (projectID) references `#__prj_projects` (id)
            on update cascade on delete cascade;

alter table `#__prj_manager_contracts_history`
    modify dat date null after id;

alter table `#__prj_manager_contracts_history`
    add exhibitors int default 0 not null;

alter table `#__prj_manager_contracts_history`
    add plan int default 0 not null;

create index `#__prj_manager_contracts_history_exhibitors_index`
    on `#__prj_manager_contracts_history` (exhibitors);

create index `#__prj_manager_contracts_history_plan_index`
    on `#__prj_manager_contracts_history` (plan);


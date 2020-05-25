alter table `#__prj_contracts`
    add info_arrival tinyint not null default 0 comment 'Отсутствуют экспонаты' after no_exhibit;

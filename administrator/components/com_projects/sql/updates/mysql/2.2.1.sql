alter table `#__prj_contracts`
    add no_exhibit tinyint not null default 0 comment 'Отсутствуют экспонаты' after logo_catalog;

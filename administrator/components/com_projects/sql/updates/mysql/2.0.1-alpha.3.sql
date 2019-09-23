create table `#__prj_user_clicks`
(
    id        int auto_increment
        primary key,
    dat       timestamp        default CURRENT_TIMESTAMP not null,
    managerID int                                        not null,
    view_from varchar(255)                               not null comment 'View, с которого был клик',
    view_to   varchar(255)                               not null comment 'View, куда делается переход',
    menu      enum ('sidebar') default 'sidebar'         not null,
    constraint `#__prj_user_clicks_#__users_id_fk`
        foreign key (managerID) references `#__users` (id)
            on update cascade on delete cascade
)
    comment 'Клики юзеров по меню';

create index `#__prj_user_clicks_dat_index`
    on `#__prj_user_clicks` (dat);

create index `#__prj_user_clicks_menu_index`
    on `#__prj_user_clicks` (menu);

create index `#__prj_user_clicks_view_from_index`
    on `#__prj_user_clicks` (view_from);

create index `#__prj_user_clicks_view_from_index_2`
    on `#__prj_user_clicks` (view_from);

create index `#__prj_user_clicks_view_to_index`
    on `#__prj_user_clicks` (view_to);


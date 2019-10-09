alter table `#__prj_user_clicks` modify `menu` enum('sidebar', 'top') default 'sidebar' not null;

delete from `#__prc_sections` where id > 307;


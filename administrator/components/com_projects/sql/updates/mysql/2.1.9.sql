alter table `#__prc_items` add is_welcome tinyint not null default 0 after is_multimedia,
                              add index `#__prc_items_is_welcome_index` (is_welcome);

update `#__prc_items` set `is_welcome` = 1 where id in (3955, 1574);

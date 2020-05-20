alter table `#__prc_items`
    add square_type tinyint null default null after is_welcome;

update `#__prc_items` set square_type = 1 where title_ru like '01.01 %';
update `#__prc_items` set square_type = 2 where title_ru like '01.02 %';
update `#__prc_items` set square_type = 3 where title_ru like '01.03 %';
update `#__prc_items` set square_type = 4 where title_ru like '01.04 %';
update `#__prc_items` set square_type = 5 where title_ru like '01.05 %';
update `#__prc_items` set square_type = 6 where title_ru like '01.06 %';
update `#__prc_items` set square_type = 7 where title_ru like '01.07 %';
update `#__prc_items` set square_type = 8 where title_ru like '01.08 %';
update `#__prc_items` set square_type = 9 where title_ru like '01.09 %';

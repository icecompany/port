create or replace view `#__prj_contract_amounts` as
select contractID, sum(price) as price, currency
from `#__prj_stat_v2`
group by contractID;

create index `#__prj_contract_items_factor_index`
    on `#__prj_contract_items` (factor);

create index `#__prj_contract_items_markup_index`
    on `#__prj_contract_items` (markup);

create index `#__prj_contract_items_value2_index`
    on `#__prj_contract_items` (value2);

create index `#__prj_contract_items_value_index`
    on `#__prj_contract_items` (value);

create or replace view `#__prj_stat_v2` as
select `i`.`id`                                                                                     AS `id`,
       `i`.`contractID`                                                                             AS `contractID`,
       `i`.`itemID`                                                                                 AS `itemID`,
       `i`.`value`                                                                                  AS `value`,
       `cost`.`cost`                                                                                AS `cost`,
       round(((((`cost`.`cost` * `i`.`value`) * ifnull(`i`.`value2`, 1)) * ifnull(`i`.`markup`, 1)) -
              (((`cost`.`cost` * `i`.`value`) * ifnull(`i`.`value2`, 1)) * (1 - `i`.`factor`))), 2) AS `price`,
       `c`.`prjID`                                                                                  AS `prjID`,
       `c`.`expID`                                                                                  AS `expID`,
       `c`.`currency`                                                                               AS `currency`,
       `c`.`status`                                                                                 AS `status`,
       `c`.`managerID`                                                                              AS `managerID`
from `#__prj_contract_items` `i`
         join `#__prj_costs` `cost` on `cost`.`id` = `i`.`id`
         left join `#__prj_contracts` `c` on `c`.`id` = `i`.`contractID`
order by i.contractID;

drop index `#__prj_contract_items_itemID_columnID_contractID_index` on `#__prj_contract_items`;

alter table `#__prj_contract_items`
    drop column updated;

alter table `#__prj_contract_items` drop foreign key `#__prj_contract_items_ibfk_3`;

alter table `#__prj_contract_items`
    add constraint `#__prj_contract_items_#__prj_contracts_id_fk`
        foreign key (contractID) references `#__prj_contracts` (id)
            on update cascade on delete cascade;

alter table `#__prj_contract_items`
    add constraint `#__prj_contract_items_#__prc_items_id_fk`
        foreign key (itemID) references `#__prc_items` (id);

create or replace view `#__prj_contract_todos_count` as
select `contractID`           AS `contractID`,
       ifnull(count(`id`), 0) AS `cnt`
from `#__prj_todos`
where ((`is_notify` = 0) and (`state` = 0))
group by `contractID`
order by null;

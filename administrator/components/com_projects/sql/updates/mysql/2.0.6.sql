create or replace view `#__prj_contracts_v2` as
select `c`.`id`                                                                              AS `id`,
       if(isnull(`p`.`contract_prefix`), ifnull(`c`.`number`, `c`.`number_free`),
          concat(`p`.`contract_prefix`, ifnull(`c`.`number`, `c`.`number_free`)))            AS `num`,
       `c`.`dat`                                                                             AS `dat`,
       `c`.`currency`                                                                        AS `currency`,
       `c`.`isCoExp`                                                                         AS `isCoExp`,
       `c`.`parentID`                                                                        AS `parentID`,
       `p`.`title_ru`                                                                        AS `project`,
       `p`.`id`                                                                              AS `projectID`,
       ifnull(`e`.`title_ru_short`, ifnull(`e`.`title_ru_full`, `e`.`title_en`))             AS `exhibitor`,
       `e`.`id`                                                                              AS `exhibitorID`,
       `e`.`title_ru_short`                                                                  AS `title_ru_short`,
       `e`.`title_ru_full`                                                                   AS `title_ru_full`,
       `e`.`title_en`                                                                        AS `title_en`,
       ifnull(`coexp`.`title_ru_short`, ifnull(`coexp`.`title_ru_full`, `coexp`.`title_en`)) AS `parent`,
       ifnull(`tdc`.`cnt`, 0)                                                                AS `todos`,
       `u`.`name`                                                                            AS `manager`,
       `c`.`managerID`                                                                       AS `managerID`,
       `s`.`title`                                                                           AS `status`,
       `s`.`weight`                                                                          AS `status_weight`,
       `c`.`status`                                                                          AS `status_code`,
       if((`c`.`currency` = 'rub'), 0, if((`c`.`currency` = 'usd'), 1, 2))                   AS `sort_amount`,
       `c`.`doc_status`                                                                      AS `doc_status`,
       ifnull(`a`.`price`, 0)                                                                AS `amount`,
       ifnull(`pay`.`payments`, 0)                                                           AS `payments`,
       (ifnull(`a`.`price`, 0) - ifnull(`pay`.`payments`, 0))                                AS `debt`,
       `c`.`payerID`                                                                         AS `payerID`,
       ifnull(`e1`.`title_ru_short`, ifnull(`e1`.`title_ru_full`, `e1`.`title_en`))          AS `payer`,
       `cmd`.`dat`                                                                           AS `plan_dat`,
       cntr.id                                                                               AS `countryID`,
       cntr.name                                                                             AS `country`
from `#__prj_contracts` `c`
         left join `#__prj_projects` `p` on `c`.`prjID` = `p`.`id`
         left join `#__prj_exp` `e` on `c`.`expID` = `e`.`id`
         left join `#__prj_exp` `coexp` on `coexp`.`id` = `c`.`parentID`
         left join `#__prj_contract_todos_count` `tdc` on `tdc`.`contractID` = `c`.`id`
         left join `#__users` `u` on `c`.`managerID` = `u`.`id`
         left join `#__prj_statuses` `s` on `s`.`code` = `c`.`status`
         left join `#__prj_contract_amounts` `a` on `c`.`id` = `a`.`contractID`
         left join `#__prj_contract_payments` `pay` on `c`.`id` = `pay`.`contractID`
         left join `#__prj_exp` `e1` on `e1`.`id` = `c`.`payerID`
         left join `#__grph_cities` ct on ct.id = e.regID
         left join `#__grph_regions` reg on ct.region_id = reg.id
         left join `#__grph_countries` cntr on reg.country_id = cntr.id
         left join `#__prj_contracts_min_dates` `cmd` on `c`.`id` = `cmd`.`contractID`;


create or replace view `#__prj_contracts_without_todos` as
select c.id as contractID, c.prjID, c.managerID
from `#__prj_contracts` c
         left join `#__prj_todos_by_contracts` t on c.id = t.contractID
where (t.id is null and c.status != 0);


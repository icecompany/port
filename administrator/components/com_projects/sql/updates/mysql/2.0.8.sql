alter table `#__prj_exp_persons`
    add accreditation tinyint default 0 not null comment 'Ответственный за аккредитацию' after main;

alter table `#__prj_exp_persons`
    add building tinyint default 0 not null comment 'Ответственный за застройку' after accreditation;

create index `#__prj_exp_persons_accreditation_index`
    on `#__prj_exp_persons` (accreditation);

create index `#__prj_exp_persons_building_index`
    on `#__prj_exp_persons` (building);


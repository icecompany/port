alter table `#__prj_contracts`
    add pvn_1 boolean default 0 not null after doc_status,
    add pvn_1a boolean default 0 not null after pvn_1,
    add pvn_1b boolean default 0 not null after pvn_1a,
    add pvn_1v boolean default 0 not null after pvn_1b,
    add pvn_1g boolean default 0 not null after pvn_1v,
    add info_catalog boolean default 0 not null after pvn_1g,
    add logo_catalog boolean default 0 not null after info_catalog,
    add index `#__prj_contracts_pvn_1_index` (pvn_1),
    add index `#__prj_contracts_pvn_1a_index` (pvn_1a),
    add index `#__prj_contracts_pvn_1b_index` (pvn_1b),
    add index `#__prj_contracts_pvn_1v_index` (pvn_1v),
    add index `#__prj_contracts_pvn_1g_index` (pvn_1g),
    add index `#__prj_contracts_pvn_info_catalog_index` (info_catalog),
    add index `#__prj_contracts_pvn_logo_catalog_index` (logo_catalog);

alter table `#__prj_exp_contacts`
    add phone_1_additional varchar(15) null default null after phone_1,
    add phone_2_additional varchar(15) null default null after phone_2,
    add fax_additional varchar(15) null default null after fax;

create temporary table `#__prj_tmp_phones` as select id from `#__prj_exp_contacts` where phone_1 like '% доб. ';
update `#__prj_exp_contacts` set phone_1 = replace(phone_1, ' доб. ', '') where id in (select id from `#__prj_tmp_phones`);
drop table `#__prj_tmp_phones`;
create temporary table `#__prj_tmp_phones` as select id from `#__prj_exp_contacts` where phone_2 like '% доб. ';
update `#__prj_exp_contacts` set phone_2 = replace(phone_2, ' доб. ', '') where id in (select id from `#__prj_tmp_phones`);
drop table `#__prj_tmp_phones`;
update `#__prj_exp_contacts`
set phone_1_additional = if(phone_1 is not null, if(locate('доб.', phone_1) <> 0, if (length(substring(phone_1, locate('доб.', phone_1)+5)) < 16, substring(phone_1, locate('доб.', phone_1)+5),null), phone_1_additional), null),
    phone_2_additional = if(phone_2 is not null, if(locate('доб.', phone_2) <> 0, if (length(substring(phone_2, locate('доб.', phone_2)+5)) < 16, substring(phone_2, locate('доб.', phone_2)+5),null), phone_2_additional), null),
    fax_additional = if(fax is not null, if(locate('доб.', fax) <> 0, if (length(substring(fax, locate('доб.', fax)+5)) < 16, substring(fax, locate('доб.', fax)+5),null), fax_additional), null);

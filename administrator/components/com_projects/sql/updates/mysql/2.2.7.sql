alter table `#__prj_contracts`
    add    invite_date            date         null default null comment 'Дата отправки приглашения',
    add    invite_outgoing_number varchar(255) null default null comment 'Исходящий номер',
    add    invite_incoming_number varchar(255) null default null comment 'Входящий номер';



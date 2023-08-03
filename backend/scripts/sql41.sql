-- Alteração por Vandré

alter table mms_prd.user_permission
add column on_vacation varchar(5) default 'false',
add column vacation_end datetime default null,
add column is_doze_trinta_e_seis varchar(5) default 'false';

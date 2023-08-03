-- Alteração por Vandré

create table mms_hml.email_content(
	id int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    description varchar(150) default '',
    subject varchar(70) default '',
    content varchar(1500) default ''
);

insert into mms_hml.email_content (description, subject, content)
values('EMAIL PADRÃO BANKMILHAS PARA LATAM', '', '');

insert into mms_hml.email_content (description, subject, content)
values('EMAIL PADRÃO BANKMILHAS PARA GOL', '', '');

insert into mms_hml.email_content (description, subject, content)
values('EMAIL PADRÃO BANKMILHAS PARA AZUL', '', '');

insert into mms_hml.email_content (description, subject, content)
values('EMAIL PADRÃO BANKMILHAS PARA TAP', '', '');

insert into mms_hml.email_content (description, subject, content)
values('EMAIL PADRÃO BANKMILHAS PARA LATAM RED/BLACK', '', '');

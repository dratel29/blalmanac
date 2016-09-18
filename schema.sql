create table room (
	id bigint not null auto_increment primary key,
	email varchar(255) not null unique,
	name varchar(255) null default null
) engine=innodb default charset=utf8;

create table board (
	id bigint not null auto_increment primary key,
	name varchar(255) null default null
) engine=innodb default charset=utf8;

create table board_rooms (
	id bigint not null auto_increment primary key,
    board_id bigint not null,
    room_id bigint not null,
    key `board_idx` (`board_id`),
    constraint `board_id_cst` foreign key (`board_id`) references `board` (`id`) on delete cascade,
    constraint `room_id_cst` foreign key (`room_id`) references `room` (`id`) on delete cascade
) engine=innodb default charset=utf8;



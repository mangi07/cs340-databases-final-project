
drop table if exists sched;

-- This table is used for both tutors and students,
--   because they can all be identified uniquely by user_name
create table sched(
	id int primary key auto_increment,
	user_name varchar(255) unique not null,
	sun char(48) not null,
	mon char(48) not null,
	tues char(48) not null,
	wed char(48) not null,
	thurs char(48) not null,
	fri char(48) not null,
	sat char(48) not null
) engine = InnoDB;



insert into sched(
	  user_name, sun, mon, tues, wed, thurs, fri, sat) values (
	  'bobby', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000'
);

insert into sched(
	  user_name, sun, mon, tues, wed, thurs, fri, sat) values (
	  'frankie', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000',
	  '111110000011111000001111100000111110000011111111'
);

-- doesn't work - maybe do it in php
select (
( select cast( (select mon from sched where user_name = 'bobby') as unsigned) ) AND
( select cast( (select mon from sched where user_name = 'frankie') as unsigned) )
);
-- to find the intersection between schedules,
-- where each bit represents a block of time:
select tb1.*, tb2.* from
(select sun, mon, tues, wed, thurs, fri, sat from sched where user_name = 'bobby') as tb1
inner join
(select sun, mon, tues, wed, thurs, fri, sat from sched where user_name = 'frankie') as tb2
on 1;


-- php use bindec() to give binary string argument such as '01010101'
-- and decbin() to convert the other way


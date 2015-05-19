-- for all these queries, replace variable input as such:
-- [php variable name]

-- inserting into student table example:
--   First try inserting username/password pair into users table:
insert into users(user_name, password) values ('studentUser1', 'studentPassword1');
-- If that returns success, then...
insert into student(user_name, fname, lname, year_born, gender, skype_id, start_date, end_date, max_rate, first_lang, second_lang) values (
'studentUser1', 'John', 'Doe', 1980, 'm', 'jd', '2015-01-01', '2015-02-01', 20, 'English', 'Korean');
-- Else let the user know the username and password could not be entered (maybe username was not unique).

-- inserting into tutor table example:
--   First try inserting username/password pair into users table:
insert into users(user_name, password) values ('tutorUser1', 'tutorPassword1');
-- If that returns success, then...
insert into tutor(user_name, fname, lname, year_born, gender, skype_id, start_date, end_date, min_rate, first_lang, second_lang) values (
'tutorUser1', 'Susie', 'Q', 1970, 'f', 'sq', '2015-01-01', '2015-02-01', 10, 'Korean', 'English');
-- Else let the user know the username and password could not be entered (maybe username was not unique).


-- Create a query for a student requesting to be matched with a certain tutor:
insert into student_wants_tutor(sid, tid) values(
(select id from student where user_name = 'studentUser1'),
(select id from tutor where user_name = 'tutorUser1')
);


-- Find all students that want to be tutored by a given tutor:
select student.user_name from student inner join
student_wants_tutor
on student.id = student_wants_tutor.sid inner join
tutor
on student_wants_tutor.tid = tutor.id
where tutor.user_name = 'tutorUser1'
order by student.user_name;


-- example of adding a connection between a student and a tutor in student_tutor table:
insert into student_tutor(sid, tid, rate, start_date) values (
(select id from student where user_name = 'studentUser1'),
(select id from tutor where user_name = 'tutorUser1'),
15,
'2015-01-15'
);
-- upon establishing this relationship between a student and tutor, delete the
--   corresponding relationship in student_wants_tutor 
--   (done this way because it's without knowing how to use triggers)
delete from student_wants_tutor where (sid, tid) = (
(select id from student where user_name = 'studentUser1_changed'),
(select id from tutor where user_name = 'tutorUser1')
);

-- end a relationship between a student and a tutor,
--   because the tutor is no longer working with that student:
delete from student_tutor where (sid, tid) = (
(select id from student where user_name = 'studentUser1_changed'),
(select id from tutor where user_name = 'tutorUser1')
);


--  where a student is looking for a certain tutor, filter search by one or more conditions:
select fname, lname, year_born, gender, start_date, end_date, min_rate, first_lang, second_lang
from tutor
where tutor.min_rate < 
(select max_rate from student where student.user_name = 'studentUser1_changed');
-- Here, the minimum the tutor will accept is less than maximum that student is willing to pay.
-- Additional/alternative conditions may be added by appending to the string that is passed to mysqli->prepare()

-- count the number of students that each tutor has:
select tutor.fname, tutor.lname, count(tutor.user_name)
from tutor inner join
student_tutor
on tutor.id = student_tutor.tid inner join
student
on student_tutor.tid = student.id
where tutor.user_name = 'tutorUser1'
group by tutor.user_name
order by tutor.lname, tutor.fname;


-- Example of updating a row in student (in php, use update_table method):
update student set fname = 'Sam', lname = 'Gonza', year_born = '1951', gender = 'm', skype_id = 'coolDude', start_date = '2015-03-12', end_date = '2016-02-01', max_rate = 20, first_lang = 'Korean', second_lang = 'English' limit 1;

-- Example of updating a row in tutor (in php, use update_table method):
update tutor set fname = 'Sam', lname = 'Changed', year_born = '1951', gender = 'm', skype_id = 'newSkype', start_date = '2015-03-12', end_date = '2016-02-01', min_rate = 10, first_lang = 'Korean', second_lang = 'English' limit 1;


-- Example of changing user_name and password:
update users set user_name = 'studentUser1_changed', password = 'studentPassword1_changed' where user_name = 'studentUser1';


-- Query to insert a weekly schedule for a given tutor/student.
insert into availability(
	  user_name, sun, mon, tues, wed, thurs, fri, sat) values (
	  'tutorUser1', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000'
);

insert into availability(
	  user_name, sun, mon, tues, wed, thurs, fri, sat) values (
	  'studentUser1_changed', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000', 
	  '111110000011111000001111100000111110000011111111', 
	  '000001111100000111110000011111000001111100000000',
	  '111110000011111000001111100000111110000011111111'
);


-- Update availability on a given day for a given tutor/student (variables day and string).
update availability set sun = '000000000000000000000000000000000000000000000000' where user_name = 'tutorUser1';

-- Clear time slots for an entire day for a tutor/student .
update availability set sun = '000000000000000000000000000000000000000000000000' where user_name = 'tutorUser1';

-- Clear all time slots for all days for a certain tutor/student.
update availability set sun =   '000000000000000000000000000000000000000000000000',
						mon =   '000000000000000000000000000000000000000000000000',
						tues =  '000000000000000000000000000000000000000000000000',
						wed =   '000000000000000000000000000000000000000000000000',
						thurs = '000000000000000000000000000000000000000000000000',
						fri =   '000000000000000000000000000000000000000000000000',
						sat =   '000000000000000000000000000000000000000000000000'
						where user_name = 'tutorUser1';

-- Get the weekly schedule of a tutor/student.
select sun, mon, tues, wed, thurs, fri from availability where user_name = 'tutorUser1';

-- Create a table that shows the schedule of a student and then a tutor.
--   The union between the given student and tutor will be determined in php.
select tb1.*, tb2.* from
(select sun, mon, tues, wed, thurs, fri, sat from availability where user_name = 'studentUser1_changed') as tb1
inner join
(select sun, mon, tues, wed, thurs, fri, sat from availability where user_name = 'tutorUser1') as tb2
on 1;

-- create query to insert a session
insert into sessions(sid, tid, start_time, end_time, rate)
values (
  (select id from student where user_name = 'studentUser1'),
  (select id from tutor where user_name = 'tutorUser1'),
  '2015-05-17 21:42:45', now(),
( select rate from student_tutor where (sid, tid) = (
  (select id from student where user_name = 'studentUser1'),
  (select id from tutor where user_name = 'tutorUser1')
) )
);


-- Create query to determine the total amount earned by a tutor.
-- (note: I'm not keeping track of payments! 
-- Just assuming tutor has been paid in full for all sessions.)
select sum(payments) from
((
  select (time_to_sec(timediff(end_time, start_time))/3600)*rate
  from sessions
  where tid = (select id from tutor where user_name = 'tutorUser1')
  
) as payments);
-- Example result: 4.6417


-- Delete a user (whether that be a student or a tutor):
delete from users where user_name = 'studentUser1_changed';
-- In this example, we're deleting a student.

-- *********!!
-- When deleting any user, delete the correct row in users table and deletions will
-- cascade properly to tables tutor, student, and student_tutor!! 
-- *********!!





-- QUERIES YET TO BE WRITTEN, IF TIME ALLOWS...
-- create query to determine the total amount owed or paid by a student...by each student
-- create query to find all sessions that happened between a certain student and tutor since a given day
-- update (edit) details of a particular session (looking it up by id primary key)
-- delete a particular session
-- delete all sessions for a particular student
-- delete all sessions for a particular tutor


--  ********PREFACE ALL FILE NAMES FOR THIS PROJECT WITH 'cs340' !!!!


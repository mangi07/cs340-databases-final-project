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

-- ADD THE FOLLOWING QUERIES TO main.php SOMEHOW !!
-- Create a query for a student requesting to be matched with a certain tutor:
insert into student_wants_tutor(sid, tid) values(
(select id from student where user_name = 'studentUser1'),
(select id from tutor where user_name = 'tutorUser1')
);
-- edit subquery: select id from tutor where id = __


-- Find all students that want to be tutored by a given tutor:
select s.fname, s.lname, s.year_born, s.gender, 
s.start_date, s.end_date, s.max_rate, s. first_lang, s.second_lang 
from cs340final_project.student as s inner join	
cs340final_project.student_wants_tutor as swt
on s.id = swt.sid inner join
cs340final_project.tutor as t
on swt.tid = t.id
where t.user_name = 'tutorUser1'
order by s.user_name

-- example of adding a connection between a student and a tutor in student_tutor table:
-- first get the average rate between student and tutor,
--   or tutor's min_rate if greater than student's max_rate
select if(max_rate > min_rate, (min_rate+max_rate)/2, min_rate) from
(select min_rate from tutor where id = 1 limit 1) as t
inner join
(select max_rate from student where id = 1 limit 1) as s;
-- use the result of above query as the rate inserted here:
insert into student_tutor(sid, tid, rate, start_date) values (
(select id from student where user_name = 'studentUser1'),
(select id from tutor where user_name = 'tutorUser1'),
15,
'2015-01-15'
);
-- actually:
insert into cs340final_project.student_tutor(sid, tid, rate, start_date) values (?, (select id from cs340final_project.tutor where user_name = ? limit 1), (select rate from cs340final_project.student_wants_tutor where (sid,tid)=(?, (select id from cs340final_project.tutor where user_name = ? limit 1)) limit 1), now());
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
-- ADJUST QUERY HERE TO REFLECT WHAT IT ACTUALLY IS IN CODE,
-- IE: SLIGHTLY DIFFERENT QUERY DEPENDING ON WHETHER IT'S A STUDENT OR TUTOR


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
  (select id from student where user_name = 'studentUser1_changed'),
  (select id from tutor where user_name = 'tutorUser1'),
  '2015-05-17 21:42:45', now(),
( select rate from student_tutor where (sid, tid) = (
  (select id from student where user_name = 'studentUser1_changed'),
  (select id from tutor where user_name = 'tutorUser1')
) )
);

-- ALSO USERS SHOULD BE ABLE TO VIEW THE SESSIONS THEY'VE HAD
select st.id as student_id, st.fname, st.lname, ss.start_time, ss.end_time,
(time_to_sec(timediff(end_time, start_time))/3600) as hours,
ss.rate,
((time_to_sec(timediff(end_time, start_time))/3600) * rate) as payment
from sessions as ss inner join
student as st on ss.sid = st.id
where ss.tid = (select id from tutor where user_name = 't1');

-- Create query to determine the total amount earned by a tutor from a given student.
-- IN PHP, CREATE DROPDOWN TO SELECT A STUDENT
-- (note: I'm not keeping track of payments! 
-- Just assuming tutor has been paid in full for all sessions.)
-- CORRECTION TO BE MADE IN sum(payments) -should be column, like sum(payments.<columnName>)
select sum(payments.total) as total from
((
  select ((time_to_sec(timediff(end_time, start_time))/3600)*rate) as total
  from sessions
  where (tid, sid) = ((select id from tutor where user_name = 't1' limit 1),
						(select id from tutor where user_name = 's1' limit 1))
  
) as payments);
-- Example result: 4.6417
-- ALSO, SIMILAR QUERY FOR STUDENT TO SEE THE TOTAL AMOUNT PAID TO ALL TUTORS.

-- QUERY FOR TUTOR TO DELETE A SESSION(S) THAT A STUDENT HAS ALREADY PAID (USE CHECKBOXES ON FORM??)

-- Delete a user (whether that be a student or a tutor):
delete from users where user_name = 'studentUser1_changed';
-- In this example, we're deleting a student.

-- *********!!
-- When deleting any user, delete the correct row in users table and deletions will
-- cascade properly to tables tutor, student, and student_tutor!! 
-- *********!!

-- You need to make sure the subqueries used with (=) to add, delete or update data return only one value. You can do this by defining the related
-- attribute as unique when you create the related table. For example for a query like below we need to make sure that -- first_name is unique in table
-- People
-- UPDATE Table1 SET p_id = (SELECT id FROM people WHERE first_name = 'Sara')



-- QUERIES YET TO BE WRITTEN, IF TIME ALLOWS...
-- create query to determine the total amount owed or paid by a student...by each student
-- create query to find all sessions that happened between a certain student and tutor since a given day
-- update (edit) details of a particular session (looking it up by id primary key)
-- delete a particular session
-- delete all sessions for a particular student
-- delete all sessions for a particular tutor


--  ********PREFACE ALL FILE NAMES FOR THIS PROJECT WITH 'cs340' !!!!


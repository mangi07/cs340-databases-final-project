

DROP TABLE IF EXISTS student_tutor;
DROP TABLE IF EXISTS student_wants_tutor;
DROP TABLE IF EXISTS student_availability;
DROP TABLE IF EXISTS tutor_availability;
DROP TABLE IF EXISTS availability;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS student;
DROP TABLE IF EXISTS tutor;
DROP TABLE IF EXISTS users;

-- Table to enforce uniqueness of (user_name, password) pairs across tutor and student tables
CREATE TABLE users(
	id INT PRIMARY KEY AUTO_INCREMENT,
	user_name VARCHAR(255) UNIQUE KEY,
	password VARCHAR(255) NOT NULL
) engine = InnoDB;
-- When a user wants to delete his/her account, just delete from users and corresponding row in tutor or student
--   should also be automatically deleted if the cascade is set up properly - write it and test it.


CREATE TABLE student(
	id INT PRIMARY KEY AUTO_INCREMENT,
	user_name VARCHAR(255) UNIQUE NOT NULL,
	fname VARCHAR(255) NOT NULL,
	lname VARCHAR(255) NOT NULL,
	year_born YEAR NOT NULL,
	gender CHAR(1),
	skype_id VARCHAR(255) NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE,
	max_rate INT NOT NULL,
	first_lang VARCHAR(255),
	second_lang VARCHAR(255),
	FOREIGN KEY (user_name) REFERENCES users(user_name) ON DELETE CASCADE ON UPDATE CASCADE
) engine = InnoDB;
-- gender can be either 'm' or 'f'
-- max_rate is the maximum rate the student is willing to pay
--   and must be in the currency designated for the entire database

CREATE TABLE tutor(
	id INT PRIMARY KEY AUTO_INCREMENT,
	user_name VARCHAR(255) UNIQUE NOT NULL,
	fname VARCHAR(255) NOT NULL,
	lname VARCHAR(255) NOT NULL,
	year_born YEAR NOT NULL,
	gender CHAR(1),
	skype_id VARCHAR(255) NOT NULL,
	start_date DATE NOT NULL,
	end_date DATE,
	min_rate INT NOT NULL,
	first_lang VARCHAR(255),
	second_lang VARCHAR(255),
	FOREIGN KEY (user_name) REFERENCES users(user_name) ON DELETE CASCADE ON UPDATE CASCADE
) engine = InnoDB;
-- gender can be either 'm' or 'f'
-- min_rate is the minimum pay the tutor is willing to accept
--   and must be in the currency designated for the entire database


CREATE TABLE student_tutor(
	sid INT,
	tid INT,
	rate INT NOT NULL,
	start_date DATE NOT NULL,
	PRIMARY KEY (sid, tid),
	FOREIGN KEY (sid) REFERENCES student(id) ON DELETE CASCADE,
	FOREIGN KEY (tid) REFERENCES tutor(id) ON DELETE CASCADE
) engine = InnoDB;
-- Each row in this table indicates a student matched with a tutor at a mutually agreed-upon
--   rate and the start_date of that agreement.

CREATE TABLE student_wants_tutor(
	sid INT,
	tid INT,
	PRIMARY KEY (sid, tid),
	FOREIGN KEY (sid) REFERENCES student(id) ON DELETE CASCADE,
	FOREIGN KEY (tid) REFERENCES tutor(id) ON DELETE CASCADE
) engine = InnoDB;
-- Each row in this table indicates a student that has requested a certain tutor.
--   The tutor will receive requests and be able to view the student profiles
--   of all students that have requested him/her, and then accept/reject one or
--   more of those requests.  If a tutor accepts a requesting student, a row will be added to
--   student_tutor to reflect this (similar to a friend request being accepted on Facebook),
--   and then the student's request should be deleted when his/her relationship is 
--   established with the tutor.

-- This table is used for both tutors and students,
--   because they can all be identified uniquely by user_name
create table availability(
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
-- Each day is a string of '0's (unavailable) and '1's (available),
--   where each 0 or 1 represents a 30-minute time segment,
--   so 48 characters in each string (sun through sat) represents a full 24 hours.
--   This is just to help students and tutors plan when to meet.


-- Table to record lessons that happen between a student and tutor.
create table sessions(
	id int primary key auto_increment,
	sid int not null,
	tid int not null,
	start_time timestamp not null,
	end_time timestamp not null,
	rate int not null,
	FOREIGN KEY (sid) REFERENCES student(id) ON DELETE CASCADE,
	FOREIGN KEY (tid) REFERENCES tutor(id) ON DELETE CASCADE
) engine = InnoDB;

-- Note: this should not be depended upon for financial record-keeping, but only as a tool to
--   assist the manager in determining what data he/she will record elsewhere (outside this database).




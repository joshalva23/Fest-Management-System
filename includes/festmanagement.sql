-- -- phpMyAdmin SQL Dump
-- -- version 4.9.2
-- -- https://www.phpmyadmin.net/
-- --
-- -- Host: localhost
-- -- Generation Time: Dec 05, 2019 at 10:28 AM
-- -- Server version: 8.0.18
-- -- PHP Version: 7.3.9

-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- SET AUTOCOMMIT = 0;
-- START TRANSACTION;
-- SET time_zone = "+00:00";


-- /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
-- /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
-- /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
-- /*!40101 SET NAMES utf8 */;

-- --
-- -- Database: `festmanagement`
-- --

-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `categories`
-- --

-- CREATE TABLE `categories` (
--   `category_id` int(11) NOT NULL,
--   `category_name` varchar(255) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --
-- -- Dumping data for table `categories`
-- --

-- INSERT INTO `categories` (`category_id`, `category_name`) VALUES
-- (98707, 'Technical'),
-- (98708, 'Brainstorming'),
-- (98709, 'Cultural'),
-- (98710, 'Sports'),
-- (98711, 'Gaming'),
-- (98712, 'Fun');

-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `events`
-- --

-- CREATE TABLE `events` (
--   `event_id` int(11) NOT NULL,
--   `event_name` varchar(255) NOT NULL,
--   `event_type` varchar(255) NOT NULL,
--   `category_id` int(11) NOT NULL,
--   `event_date` date NOT NULL,
--   `event_fee` int(11) NOT NULL,
--   `event_desc` text NOT NULL,
--   `organiser_id` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --
-- -- Dumping data for table `events`
-- --


-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `logs`
-- --

-- CREATE TABLE `logs` (
--   `log_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
--   `log_user` varchar(255) NOT NULL,
--   `log_message` text NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --
-- -- Dumping data for table `logs`
-- --


-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `organisers`
-- --

-- CREATE TABLE `organisers` (
--   `organiser_id` int PRIMARY KEY AUTO_INCREMENT NOT NULL,
--   `organiser_name` varchar(255) NOT NULL,
--   `organiser_phone` varchar(10) NOT NULL,
--   `organiser_email` VARCHAR(255) UNIQUE NOT NULL,
--   `organiser_password` VARCHAR(255) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;



-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `participants`
-- --

-- CREATE TABLE `participants` (
--   `participant_id` int(11) NOT NULL,
--   `participant_name` varchar(255) NOT NULL,
--   `participant_email` varchar(255) NOT NULL,
--   `participant_phone` varchar(10) NOT NULL,
--   `registered_by` varchar(255) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --
-- -- Dumping data for table `participants`
-- --


-- --
-- -- Triggers `participants`
-- --
-- DELIMITER $$
-- CREATE TRIGGER `log_participant` AFTER INSERT ON `participants` FOR EACH ROW INSERT INTO logs (log_user, log_message)
-- VALUES (NEW.registered_by, 'Registered a participant')
-- $$
-- DELIMITER ;

-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `registrations`
-- --

-- CREATE TABLE `registrations` (
--   `participant_id` int(11) NOT NULL,
--   `event_id` int(11) NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --
-- -- Dumping data for table `registrations`
-- --


-- -- --------------------------------------------------------

-- --
-- -- Table structure for table `users`
-- --

-- CREATE TABLE `users` (
--   `user_id` int PRIMARY KEY AUTO_INCREMENT NOT NULL,
--   `full_name` varchar(255) NOT NULL,
--   `email` varchar(255) UNIQUE NOT NULL,
--   `pass` varchar(255) NOT NULL,
--   `phone` varchar(10) NOT NULL,
--   `contribution` int(11) DEFAULT '0'
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ALTER TABLE `users`
--   ADD COLUMN `status` ENUM('pending', 'approved') DEFAULT 'pending';

-- -- Create Table Admin
-- CREATE TABLE `admin` (
--   `admin_id` int PRIMARY KEY AUTO_INCREMENT NOT NULL,
--   `user_id` int NOT NULL
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --
-- -- Triggers `users`
-- --
-- DELIMITER $$
-- CREATE TRIGGER `log_user` AFTER INSERT ON `users` FOR EACH ROW INSERT INTO logs (log_user, log_message)
-- VALUES (NEW.email, 'Signed up as a user')
-- $$
-- DELIMITER ;

-- --
-- -- Indexes for dumped tables
-- --

-- --
-- -- Indexes for table `categories`
-- --
-- ALTER TABLE `categories`
--   ADD PRIMARY KEY (`category_id`);

-- --
-- -- Indexes for table `events`
-- --
-- ALTER TABLE `events`
--   ADD PRIMARY KEY (`event_id`),
--   ADD KEY `category_id` (`category_id`),
--   ADD KEY `organiser_id` (`organiser_id`);

-- --
-- -- Indexes for table `logs`
-- --
-- ALTER TABLE `logs`
--   ADD KEY `log_user` (`log_user`);

-- --
-- -- Indexes for table `organisers`
-- --

-- --
-- -- Indexes for table `participants`
-- --
-- ALTER TABLE `participants`
--   ADD PRIMARY KEY (`participant_id`),
--   ADD KEY `registered_by` (`registered_by`);

-- --
-- -- Indexes for table `registrations`
-- --
-- ALTER TABLE `registrations`
--   ADD KEY `participant_id` (`participant_id`),
--   ADD KEY `event_id` (`event_id`);


-- --
-- -- AUTO_INCREMENT for dumped tables
-- --

-- --
-- -- AUTO_INCREMENT for table `categories`
-- --
-- ALTER TABLE `categories`
--   MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98713;

-- --
-- -- AUTO_INCREMENT for table `events`
-- --
-- ALTER TABLE `events`
--   MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=313814;

-- --
-- -- AUTO_INCREMENT for table `organisers`
-- --
-- ALTER TABLE `organisers`
--   MODIFY `organiser_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67548;

-- --
-- -- AUTO_INCREMENT for table `participants`
-- --
-- ALTER TABLE `participants`
--   MODIFY `participant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213649;

-- --
-- -- Constraints for dumped tables
-- --

-- --
-- -- Constraints for table `events`
-- --
-- ALTER TABLE `events`
--   ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT,
--   ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`organiser_id`) REFERENCES `organisers` (`organiser_id`) ON DELETE RESTRICT;

-- --
-- -- Constraints for table `logs`
-- --
-- -- ALTER TABLE `logs`
-- --   ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`log_user`) REFERENCES `users` (`email`) ON DELETE RESTRICT;

-- --
-- -- Constraints for table `participants`
-- --
-- ALTER TABLE `participants`
--   ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `users` (`email`) ON DELETE RESTRICT;

-- --
-- -- Constraints for table `registrations`
-- --
-- ALTER TABLE `registrations`
--   ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`participant_id`) ON DELETE CASCADE,
--   ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE RESTRICT;



-- ALTER TABLE `admin`
--   ADD CONSTRAINT `fk_user_id`
--   FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
--   ON DELETE CASCADE
--   ON UPDATE CASCADE;

-- COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

PostgreSQL SQL Dump
Converted from MySQL Dump
Host: localhost
Generation Time: Dec 05, 2019 at 10:28 AM
Server version: 13.x
PHP Version: 7.3.9

Drop existing tables if they exist
DROP TABLE IF EXISTS admin CASCADE;
DROP TABLE IF EXISTS logs CASCADE;
DROP TABLE IF EXISTS registrations CASCADE;
DROP TABLE IF EXISTS participants CASCADE;
DROP TABLE IF EXISTS events CASCADE;
DROP TABLE IF EXISTS organisers CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Create table `users`
CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    pass VARCHAR(255) NOT NULL,
    phone VARCHAR(10) NOT NULL,
    contribution INTEGER DEFAULT 0,
    status VARCHAR(20) CHECK (status IN ('pending', 'approved')) DEFAULT 'pending'
);

-- Create table `organisers`
CREATE TABLE organisers (
    organiser_id SERIAL PRIMARY KEY,
    organiser_name VARCHAR(255) NOT NULL,
    organiser_phone VARCHAR(10) NOT NULL,
    organiser_email VARCHAR(255) UNIQUE NOT NULL,
    organiser_password VARCHAR(255) NOT NULL
);

-- Create table `categories`
CREATE TABLE categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL
);

-- Insert data into `categories`
INSERT INTO categories (category_name) VALUES
('Technical'),
('Brainstorming'),
('Cultural'),
('Sports'),
('Gaming'),
('Fun');

-- Create table `events`
CREATE TABLE events (
    event_id SERIAL PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    event_type VARCHAR(255) NOT NULL,
    category_id INTEGER NOT NULL REFERENCES categories(category_id) ON DELETE RESTRICT,
    event_date DATE NOT NULL,
    event_fee INTEGER NOT NULL,
    event_desc TEXT NOT NULL,
    organiser_id INTEGER NOT NULL REFERENCES organisers(organiser_id) ON DELETE RESTRICT
);

-- Create table `logs`
CREATE TABLE logs (
    log_time TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    log_user VARCHAR(255) NOT NULL,
    log_message TEXT NOT NULL
);



-- Create table `participants`
CREATE TABLE participants (
    participant_id SERIAL PRIMARY KEY,
    participant_name VARCHAR(255) NOT NULL,
    participant_email VARCHAR(255) NOT NULL,
    participant_phone VARCHAR(10) NOT NULL,
    registered_by VARCHAR(255) NOT NULL REFERENCES users(email) ON DELETE RESTRICT
);

-- Create table `registrations`
CREATE TABLE registrations (
    participant_id INTEGER NOT NULL REFERENCES participants(participant_id) ON DELETE CASCADE,
    event_id INTEGER NOT NULL REFERENCES events(event_id) ON DELETE RESTRICT,
    PRIMARY KEY (participant_id, event_id)
);



-- Create table `admin`
CREATE TABLE admin (
    admin_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Trigger function to log participant registrations
CREATE OR REPLACE FUNCTION log_participant() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO logs (log_user, log_message) VALUES (NEW.registered_by, 'Registered a participant');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger for `participants`
CREATE TRIGGER log_participant
AFTER INSERT ON participants
FOR EACH ROW
EXECUTE FUNCTION log_participant();

-- Trigger function to log user signups
CREATE OR REPLACE FUNCTION log_user() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO logs (log_user, log_message) VALUES (NEW.email, 'Signed up as a user');
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger for `users`
CREATE TRIGGER log_user
AFTER INSERT ON users
FOR EACH ROW
EXECUTE FUNCTION log_user();

-- Function to update user contributions based on event fees
CREATE OR REPLACE FUNCTION update_contribution() RETURNS VOID AS $$
BEGIN
    -- Update user contributions based on total fee collected through registrations
    UPDATE users
    SET contribution = (
        SELECT COALESCE(SUM(e.event_fee), 0)
        FROM registrations r
        JOIN events e ON r.event_id = e.event_id
        JOIN participants p ON r.participant_id = p.participant_id
        WHERE p.registered_by = users.email
    );
END;
$$ LANGUAGE plpgsql;

-- Trigger function to call update_contribution
CREATE OR REPLACE FUNCTION trigger_update_contribution() RETURNS TRIGGER AS $$
BEGIN
    -- Call the function to update contributions
    PERFORM update_contribution();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create the trigger for the registrations table
CREATE TRIGGER update_contribution_trigger
AFTER INSERT ON registrations
FOR EACH ROW
EXECUTE FUNCTION trigger_update_contribution();

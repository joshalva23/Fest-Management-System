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

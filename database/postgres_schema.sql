-- GymBRO / PostgreSQL schema
-- Utworz baze poleceniem:
-- CREATE DATABASE gymbro_db;
-- Nastepnie polacz sie z nia i uruchom ten plik.

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    age INTEGER,
    height_cm INTEGER,
    weight_kg NUMERIC(5,2),
    training_level VARCHAR(50),
    training_experience VARCHAR(50),
    goal VARCHAR(100),
    bio TEXT,
    avatar_path VARCHAR(255),
    onboarding_completed BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE user_profiles ADD COLUMN IF NOT EXISTS training_experience VARCHAR(50);
ALTER TABLE user_profiles ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255);
ALTER TABLE user_profiles ADD COLUMN IF NOT EXISTS onboarding_completed BOOLEAN DEFAULT FALSE;

CREATE TABLE IF NOT EXISTS friendships (
    id SERIAL PRIMARY KEY,
    requester_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    receiver_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (requester_id, receiver_id),
    CHECK (status IN ('pending', 'accepted', 'rejected')),
    CHECK (requester_id <> receiver_id)
);

CREATE TABLE IF NOT EXISTS gyms (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    name VARCHAR(150) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address VARCHAR(200),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS exercises (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    name VARCHAR(150) NOT NULL,
    muscle_group VARCHAR(100) NOT NULL,
    equipment VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS workout_events (
    id SERIAL PRIMARY KEY,
    creator_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    gym_id INTEGER REFERENCES gyms(id) ON DELETE SET NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    start_time TIME NOT NULL,
    max_participants INTEGER DEFAULT 2,
    status VARCHAR(30) DEFAULT 'planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (status IN ('planned', 'completed', 'cancelled'))
);

CREATE TABLE IF NOT EXISTS workout_event_participants (
    id SERIAL PRIMARY KEY,
    event_id INTEGER REFERENCES workout_events(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (event_id, user_id)
);

CREATE TABLE IF NOT EXISTS training_plans (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    level VARCHAR(50),
    goal VARCHAR(100),
    visibility VARCHAR(20) DEFAULT 'private',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (visibility IN ('private', 'public'))
);

CREATE TABLE IF NOT EXISTS training_plan_days (
    id SERIAL PRIMARY KEY,
    plan_id INTEGER REFERENCES training_plans(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    day_order INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS training_plan_exercises (
    id SERIAL PRIMARY KEY,
    day_id INTEGER REFERENCES training_plan_days(id) ON DELETE CASCADE,
    exercise_id INTEGER REFERENCES exercises(id) ON DELETE CASCADE,
    sets INTEGER NOT NULL,
    reps VARCHAR(50) NOT NULL,
    rest_seconds INTEGER,
    notes TEXT,
    exercise_order INTEGER DEFAULT 1
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_workout_events_event_date ON workout_events(event_date);
CREATE INDEX IF NOT EXISTS idx_training_plans_user_id ON training_plans(user_id);
CREATE INDEX IF NOT EXISTS idx_training_plans_visibility ON training_plans(visibility);
CREATE INDEX IF NOT EXISTS idx_friendships_requester_id ON friendships(requester_id);
CREATE INDEX IF NOT EXISTS idx_friendships_receiver_id ON friendships(receiver_id);

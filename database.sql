/* 
 * ======================================================
 *                    DATABASE CREATION              
 * ======================================================
 */

DROP DATABASE IF EXISTS ninja_db;

/* 
 * ======================================================
 *              CREATE A NEW DATABASE IF NOT EXISTS     
 * ======================================================
 */
CREATE DATABASE ninja_db;

/* 
 * ======================================================
 *              SELECT THE CREATED DATABASE             
 * ======================================================
 */
USE ninja_db;

/* 
 * ======================================================
 *                    USERS TABLE                       
 * ======================================================
 * This table stores user information:
 * - id: auto-incrementing primary key
 * - name: full name of the user
 * - username: unique login username
 * - password: hashed password for authentication
 * - created_at: timestamp when the record is created
 * - updated_at: timestamp when the record is updated
 */
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

/* 
 * ======================================================
 *                    GAMES TABLE                       
 * ======================================================
 * This table stores game sessions:
 * - id: unique game ID
 * - user_id: reference to the creator of the game
 * - link: unique URL identifier for the game
 * - created_at: timestamp when the game was created
 */
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    link VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

/* 
 * ======================================================
 *                GAME_RECORDS TABLE                    
 * ======================================================
 * This table stores gameplay records:
 * - id: unique record ID
 * - game_id: reference to the game played
 * - user_id: reference to the player
 * - score: score achieved in the game
 * - played_seconds: how long the game was played (in seconds)
 */
CREATE TABLE game_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    user_id INT NOT NULL,
    score INT DEFAULT 0,
    played_seconds INT NOT NULL,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

/* 
 * ======================================================
 *              INSERT INITIAL ADMIN USER               
 * ======================================================
 * Adding the default admin user with hashed password
 * USERNAME: iqbolshoh
 * PASSWORD: IQBOLSHOH
 */
INSERT INTO users (name, username, password)
VALUES 
('Iqbolshoh Ilhomjonov', 'iqbolshoh', '$2y$10$gIKUrsLRB.U7ee9Fv9nib.di2NgMYvAeqqWGoB5aFXpHoxIv/igkW');

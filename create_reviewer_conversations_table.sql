-- SQL for reviewer_conversations table
CREATE TABLE IF NOT EXISTS reviewer_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    response TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX(user_id),
    FOREIGN KEY (user_id) REFERENCES students(id) ON DELETE CASCADE
);
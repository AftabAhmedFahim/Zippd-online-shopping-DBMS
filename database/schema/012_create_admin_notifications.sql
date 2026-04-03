-- admin_notifications
CREATE TABLE admin_notifications (
    notification_id INT IDENTITY(1,1) PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message VARCHAR(MAX) NOT NULL,
    related_user_id INT NULL,
    event_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
    is_read BIT NOT NULL DEFAULT 0
);
-- ============================================
-- Admin Users page SQL queries
-- ============================================

-- 1) Read-only users directory
SELECT user_id, full_name, email, phone, gender, address, created_at, updated_at
FROM users
ORDER BY user_id ASC;


-- 2) Users directory search (bind all placeholders with %q%)
SELECT user_id, full_name, email, phone, gender, address, created_at, updated_at
FROM users
WHERE full_name LIKE ?
   OR email LIKE ?
   OR phone LIKE ?
   OR address LIKE ?
   OR CONVERT(VARCHAR(10), created_at, 23) LIKE ?
ORDER BY user_id ASC;


-- 3) Latest account deletion updates for admin
SELECT TOP (8) notification_id, event_type, title, message, related_user_id, event_at, is_read
FROM admin_notifications
WHERE event_type = 'user_deleted'
ORDER BY event_at DESC, notification_id DESC;


-- 4) Trigger-backed notification setup for user account deletion
IF OBJECT_ID('dbo.admin_notifications', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.admin_notifications (
        notification_id INT IDENTITY(1,1) PRIMARY KEY,
        event_type VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        message VARCHAR(MAX) NOT NULL,
        related_user_id INT NULL,
        event_at DATETIME NOT NULL DEFAULT SYSDATETIME(),
        is_read BIT NOT NULL DEFAULT 0
    );
END;
GO

IF OBJECT_ID('dbo.trg_users_after_delete_admin_notification', 'TR') IS NOT NULL
BEGIN
    DROP TRIGGER dbo.trg_users_after_delete_admin_notification;
END;
GO

CREATE TRIGGER dbo.trg_users_after_delete_admin_notification
ON dbo.users
AFTER DELETE
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO dbo.admin_notifications (event_type, title, message, related_user_id, event_at, is_read)
    SELECT
        'user_deleted',
        'User Account Deleted',
        CONCAT(
            'User ', COALESCE(d.full_name, 'Unknown User'),
            ' (ID: ', CAST(d.user_id AS VARCHAR(20)),
            ', Email: ', COALESCE(d.email, 'N/A'),
            ') deleted their account.'
        ),
        d.user_id,
        SYSDATETIME(),
        0
    FROM deleted AS d;
END;
GO

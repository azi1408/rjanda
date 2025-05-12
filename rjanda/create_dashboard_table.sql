-- Create dashboard table
CREATE TABLE IF NOT EXISTS dashboard (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date_recorded DATE,
    total_orders INT,
    completed_orders INT,
    total_sales DECIMAL(10,2),
    period_type ENUM('daily', 'weekly', 'monthly'),
    period_value VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create indexes if they don't exist
SET @exist := (SELECT COUNT(1) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE table_schema = DATABASE()
    AND table_name = 'dashboard' 
    AND index_name = 'idx_dashboard_date');

SET @sql := IF(@exist = 0, 
    'CREATE INDEX idx_dashboard_date ON dashboard(date_recorded)',
    'SELECT "Index idx_dashboard_date already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(1) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE table_schema = DATABASE()
    AND table_name = 'dashboard' 
    AND index_name = 'idx_dashboard_period');

SET @sql := IF(@exist = 0, 
    'CREATE INDEX idx_dashboard_period ON dashboard(period_type, period_value)',
    'SELECT "Index idx_dashboard_period already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop existing procedure if it exists
DROP PROCEDURE IF EXISTS update_dashboard_stats;

-- Create stored procedure to update dashboard data
DELIMITER //

CREATE PROCEDURE update_dashboard_stats()
BEGIN
    -- Clear old data
    DELETE FROM dashboard WHERE date_recorded < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);
    
    -- Insert daily stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        DATE(created_at) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(guests * 500) as total_sales, -- Assuming 500 per guest as base price
        'daily' as period_type,
        DATE_FORMAT(created_at, '%Y-%m-%d') as period_value
    FROM catering_orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE(created_at);

    -- Insert weekly stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        MAX(DATE(created_at)) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(guests * 500) as total_sales, -- Assuming 500 per guest as base price
        'weekly' as period_type,
        DATE_FORMAT(created_at, '%Y-%u') as period_value
    FROM catering_orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE_FORMAT(created_at, '%Y-%u');

    -- Insert monthly stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        MAX(DATE(created_at)) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(guests * 500) as total_sales, -- Assuming 500 per guest as base price
        'monthly' as period_type,
        DATE_FORMAT(created_at, '%Y-%m') as period_value
    FROM catering_orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m');
END //

DELIMITER ;

-- Drop existing event if it exists
DROP EVENT IF EXISTS update_dashboard_daily;

-- Create event to update dashboard data daily
CREATE EVENT update_dashboard_daily
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY
DO CALL update_dashboard_stats();

-- Initial data population
CALL update_dashboard_stats(); 
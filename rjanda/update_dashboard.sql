-- Drop existing procedure if it exists
DROP PROCEDURE IF EXISTS update_dashboard_stats;

DELIMITER //

CREATE PROCEDURE update_dashboard_stats()
BEGIN
    -- Clear old data
    DELETE FROM dashboard WHERE date_recorded < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);
    
    -- Insert daily stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        DATE(order_date) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(total_price) as total_sales,
        'daily' as period_type,
        DATE_FORMAT(order_date, '%Y-%m-%d') as period_value
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE(order_date);

    -- Insert weekly stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        MAX(DATE(order_date)) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(total_price) as total_sales,
        'weekly' as period_type,
        DATE_FORMAT(order_date, '%Y-%u') as period_value
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE_FORMAT(order_date, '%Y-%u');

    -- Insert monthly stats
    INSERT INTO dashboard (date_recorded, total_orders, completed_orders, total_sales, period_type, period_value)
    SELECT 
        MAX(DATE(order_date)) as date_recorded,
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
        SUM(total_price) as total_sales,
        'monthly' as period_type,
        DATE_FORMAT(order_date, '%Y-%m') as period_value
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    GROUP BY DATE_FORMAT(order_date, '%Y-%m');
END //

DELIMITER ;

-- Run the procedure to update the dashboard
CALL update_dashboard_stats(); 
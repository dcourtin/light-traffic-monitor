# Light Traffic Monitor
This a simple traffic monitoring class i use in my blog. See it like a smart counter.

#### Use it if:
- You want a small piece of code to handle this parts
- You have no strategic needs
- You have low traffic website

## Features
- Set a session with a unique id and a time to live (30 minutes).
- Don't store bots requests
- Methods to store and retrieve traffic data.

## Usage
Ideally, you should have a database with a table like this:

```sql
CREATE TABLE page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    page VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    timestamp DATETIME NOT NULL
);
```

Then you can use the class like this:

```php
$monitor = new TrafficMonitor(LOCALHOST, DB_NAME, DB_USER, DB_PASS, DB_PORT);
$monitor->trackVisit();
```

### Generate report
```php
$report = $monitor->generateReport();
foreach ($report as $row) {
    dump("Page: {$row['page']}, Views: {$row['views']}") ;
}
```

### Generate for a page in particular
```php
$monitor->generateReportForPage($page);
```

Review the class for more.

:warning: There is not so much work on this, so use it at your own risks.

## Prerequisites
PHP/PDO/SQL 
- tested on PHP 8.2
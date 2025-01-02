<?php

namespace D4v\LightTrafficMonitor;

use PDO;

class TrafficMonitor
{
    private $pdo;

    private $bots = [
            'Googlebot',
            'Googlebot-Image',
            'Googlebot-News',
            'Googlebot-Video',
            'AdsBot-Google',
            'Mediapartners-Google',
            'Bingbot',
            'Adidxbot',
            'BingPreview',
            'Slurp',
            'Yahoo-MMCrawler',
            'Baiduspider',
            'Baiduspider-image',
            'Baiduspider-video',
            'Baiduspider-news',
            'YandexBot',
            'YandexImages',
            'YandexVideo',
            'YandexMobileBot',
            'YandexAccessibilityBot',
            'DuckDuckBot',
            'ecosia-robot',
            'SeznamBot',
            'Sogou Spider',
            'facebookexternalhit',
            'Facebot',
            'Twitterbot',
            'LinkedInBot',
            'Pinterestbot',
            'Instagram',
            'AhrefsBot',
            'SEMRushBot',
            'Majestic',
            'Moz',
            'Screaming Frog',
            'DotBot',
            'SiteAuditBot',
            'UptimeRobot',
            'Site24x7',
            'SerpstatBot',
            'Botify',
            'DeepCrawl',
            'PostmanRuntime',
            'ApacheBench',
            'curl',
            'Wget',
            'Pingdom',
            'GTmetrix',
            'Google-PageSpeedInsights',
            'Checkly',
            'BrowserStack',
            'StatusCake',
            'TelegramBot',
            'Slackbot',
            'SkypeBot',
            'WhatsApp',
            'Google-Translate',
            'BingPreview',
            'CiteSeerXBot',
            'SemanticScholarBot',
            'ArchiveBot',
            'ia_archiver',
            'Applebot',
            'Amazonbot',
            'PetalBot',
            'MJ12bot',
            'TurnitinBot',
            'MegaIndex.ru',
            'Spinn3r',
            'BLEXBot',
            'HTTrack',
            'SemrushBot',
        ];
        
    public function __construct($dbHost, $dbName, $dbUser, $dbPass, $dbPort)
    {
        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4;port=$dbPort";
        $this->pdo = new PDO($dsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public function trackVisit()
    {
        session_start();
        $this->manageSession();
        $userId = $this->getUserId();
        $this->recordPageView($userId);
    }

    private function manageSession()
    {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        } elseif (time() - $_SESSION['last_activity'] > 1800) { // 30 min d'inactivité
            session_destroy();
            session_start();
            $_SESSION['last_activity'] = time();
        }
    }

    private function getUserId()
    {
        if (!isset($_COOKIE['user_id'])) {
            $userId = uniqid();
            setcookie('user_id', $userId, time() + (86400 * 30), '/'); // Cookie valable 30 jours
        } else {
            $userId = $_COOKIE['user_id'];
        }
        return $userId;
    }

    private function recordPageView($userId)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $page = $_SERVER['REQUEST_URI'] ?? '/';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if ($this->isBot($userAgent)) {
            return; // Ignore bots
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO page_views (user_id, page, ip, user_agent, timestamp)
            VALUES (:user_id, :page, :ip, :user_agent, :timestamp)
        ");
        $stmt->execute([
            'user_id' => $userId,
            'page' => $page,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    private function isBot($userAgent)
    {
        $isBot = false;
        foreach ($this->bots as $bot) {
            if (preg_match('/\b' . preg_quote($bot, '/') . '\b/i', $userAgent)) {
                $isBot = true;
                break;
            }
        }
        return $isBot;
    }

    public function generateReport()
    {
        $stmt = $this->pdo->query("
            SELECT page, COUNT(*) as views 
            FROM page_views 
            GROUP BY page 
            ORDER BY views DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateReportByUser()
    {
        $stmt = $this->pdo->query("
            SELECT user_id, COUNT(*) as views 
            FROM page_views 
            GROUP BY user_id 
            ORDER BY views DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateReportByIp()
    {
        $stmt = $this->pdo->query("
            SELECT ip, COUNT(*) as views 
            FROM page_views 
            GROUP BY ip 
            ORDER BY views DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generateReportForPage($page)
    {
        $stmt = $this->pdo->prepare("
            SELECT page, COUNT(*) as views
            FROM page_views
            WHERE page = :page
            ");
        $stmt->execute(['page' => $page]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
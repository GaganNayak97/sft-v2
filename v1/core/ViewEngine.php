<?php
/**
 * Global View & Component Engine
 * Provides function-based rendering across templates, sections, layouts, and components.
 * Eliminates manual require/include spaghetti across the codebase.
 */

if (!function_exists('app_config')) {
    function app_config(?string $key = null, $default = null) {
        static $config = null;
        if ($config === null) {
            $config = require __DIR__ . '/../../config/database.php';
        }
        if ($key === null) {
            return $config;
        }
        $parts = explode('.', $key);
        $curr = $config;
        foreach ($parts as $p) {
            if (!isset($curr[$p])) {
                return $default;
            }
            $curr = $curr[$p];
        }
        return $curr;
    }
}

if (!function_exists('render_file')) {
    function render_file(string $filePath, array $data = []): void {
        if (!file_exists($filePath)) {
            echo "<!-- View file not found: " . htmlspecialchars($filePath) . " -->";
            return;
        }
        extract($data, EXTR_SKIP);
        include $filePath;
    }
}

if (!function_exists('component')) {
    function component(string $name, array $data = []): void {
        $path = __DIR__ . '/../components/' . $name . '.php';
        render_file($path, $data);
    }
}

if (!function_exists('section')) {
    function section(string $name, array $data = []): void {
        $path = __DIR__ . '/../sections/' . $name . '.php';
        render_file($path, $data);
    }
}

if (!function_exists('layout')) {
    function layout(string $name, array $data = []): void {
        $path = __DIR__ . '/../layouts/' . $name . '.php';
        render_file($path, $data);
    }
}

if (!function_exists('template')) {
    function template(string $name, array $data = []): void {
        $path = __DIR__ . '/../templates/' . $name . '.php';
        render_file($path, $data);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $cleanPath = ltrim($path, '/');
        $fullPath = __DIR__ . '/../' . $cleanPath;
        $ver = file_exists($fullPath) ? (string)filemtime($fullPath) : '1.0';
        return '?asset=' . urlencode($cleanPath) . '&v=' . $ver;
    }
}

if (!function_exists('url')) {
    function url(string $page = 'home', array $params = []): string {
        $query = http_build_query(array_merge(['page' => $page], $params));
        return 'index.php?' . $query;
    }
}

if (!function_exists('format_currency')) {
    function format_currency($amount, int $decimals = 2): string {
        $sym = app_config('app.currency', '₹');
        return $sym . number_format((float)$amount, $decimals);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array {
        return \App\Core\Auth::user();
    }
}

if (!function_exists('flash_get')) {
    function flash_get(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['flash'])) {
            $msg = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $msg;
        }
        return null;
    }
}

if (!function_exists('flash_set')) {
    function flash_set(string $type, string $message): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('svg_icon')) {
    function svg_icon(string $name, string $class = '', int $size = 20): string {
        $attrs = 'width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-'.$name.' '.$class.'"';
        
        switch ($name) {
            case 'home':
                return '<svg '.$attrs.'><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>';
            case 'balance':
            case 'trending':
            case 'trending-up':
            case 'trend-up':
            case 'trading':
                return '<svg '.$attrs.'><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>';
            case 'deposit':
            case 'arrow-down-left':
                return '<svg '.$attrs.'><line x1="17" y1="7" x2="7" y2="17"></line><polyline points="17 17 7 17 7 7"></polyline></svg>';
            case 'withdraw':
            case 'arrow-up-right':
                return '<svg '.$attrs.'><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>';
            case 'crown':
            case 'reward':
                return '<svg '.$attrs.'><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7z"></path><path d="M5 20h14"></path></svg>';
            case 'usdt':
            case 'crypto':
            case 'globe':
                return '<svg '.$attrs.'><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>';
            case 'history':
            case 'book':
                return '<svg '.$attrs.'><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
            case 'shield':
            case 'security':
                return '<svg '.$attrs.'><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>';
            case 'shield-check':
                return '<svg '.$attrs.'><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>';
            case 'settings':
                return '<svg '.$attrs.'><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>';
            case 'chat':
            case 'help':
                return '<svg '.$attrs.'><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
            case 'bell':
                return '<svg '.$attrs.'><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>';
            case 'user':
                return '<svg '.$attrs.'><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
            case 'gift':
            case 'referral':
                return '<svg '.$attrs.'><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>';
            case 'search':
                return '<svg '.$attrs.'><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
            case 'logout':
                return '<svg '.$attrs.'><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>';
            case 'copy':
                return '<svg '.$attrs.'><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>';
            case 'share':
                return '<svg '.$attrs.'><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>';
            case 'check':
            case 'check-circle':
                return '<svg '.$attrs.'><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            case 'refresh':
                return '<svg '.$attrs.'><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>';
            case 'lock':
                return '<svg '.$attrs.'><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
            case 'unlock':
                return '<svg '.$attrs.'><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>';
            case 'calculator':
                return '<svg '.$attrs.'><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><path d="M16 10h.01M12 10h.01M8 10h.01M12 14h.01M8 14h.01M12 18h.01M8 18h.01"></path></svg>';
            case 'plus':
                return '<svg '.$attrs.'><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
            case 'alert':
            case 'alert-triangle':
                return '<svg '.$attrs.'><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            case 'credit-card':
                return '<svg '.$attrs.'><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>';
            case 'sparkles':
                return '<svg '.$attrs.'><path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"></path></svg>';
            case 'whatsapp':
                return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="currentColor" class="'.$class.'"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m.01 1.67c2.2 0 4.26.86 5.82 2.42a8.23 8.23 0 0 1 2.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.39-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24M8.53 7.33c-.14 0-.36.05-.54.26-.19.2-.72.7-.72 1.71s.73 1.99.84 2.13c.1.14 1.44 2.2 3.5 3.08.49.21.87.33 1.17.43.5.16.95.14 1.31.08.4-.06 1.23-.5 1.4-.99.18-.49.18-.9.12-.99-.05-.09-.19-.15-.4-.25s-1.23-.61-1.42-.68c-.19-.07-.33-.11-.47.11s-.54.68-.66.82c-.12.14-.24.16-.45.05-.21-.1-.89-.33-1.69-1.05-.62-.56-1.04-1.24-1.16-1.45-.12-.21-.01-.33.09-.43.09-.09.21-.25.32-.37.1-.12.14-.21.21-.35.07-.14.04-.26-.02-.37-.06-.11-.47-1.14-.65-1.56-.17-.41-.35-.35-.48-.36z"/></svg>';
            case 'telegram':
                return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="currentColor" class="'.$class.'"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 0 0-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>';
            case 'candlestick':
            case 'candles':
                return '<svg '.$attrs.'><line x1="9" y1="2" x2="9" y2="22"></line><rect x="6" y="6" width="6" height="11" rx="1.5" fill="currentColor" fill-opacity="0.25"></rect><line x1="17" y1="4" x2="17" y2="20"></line><rect x="14" y="9" width="6" height="7" rx="1.5" fill="currentColor"></rect></svg>';
            case 'line-chart':
            case 'chart-line':
                return '<svg '.$attrs.'><polyline points="22 12 17 7 11 13 7 9 2 14"></polyline><path d="M22 12v7a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-5"></path></svg>';
            case 'zoom-in':
                return '<svg '.$attrs.'><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>';
            case 'zoom-out':
                return '<svg '.$attrs.'><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>';
            case 'maximize':
            case 'fit':
            case 'reset-view':
                return '<svg '.$attrs.'><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>';
            case 'orderbook':
            case 'depth':
            case 'layers':
                return '<svg '.$attrs.'><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>';
            case 'zap':
            case 'lightning':
                return '<svg '.$attrs.'><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>';
            case 'btc':
            case 'bitcoin':
                return '<svg '.$attrs.' viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9 7h4a2.5 2.5 0 0 1 2 4 2.5 2.5 0 0 1-2 4H9m0-8v8m2-8V5m0 14v-2m-2-4h4.5"></path></svg>';
            case 'eth':
            case 'ethereum':
                return '<svg '.$attrs.' viewBox="0 0 24 24"><polygon points="12 2 4.5 12.5 12 16.5 19.5 12.5 12 2"></polygon><polygon points="12 17.5 4.5 13.5 12 22 19.5 13.5 12 17.5"></polygon></svg>';
            case 'gold':
            case 'gold-ingot':
                return '<svg '.$attrs.'><path d="M4 14l3-7h10l3 7H4z"></path><path d="M2 18h20v-4H2v4z"></path></svg>';
            case 'sol':
            case 'solana':
                return '<svg '.$attrs.'><path d="M4 6.5h13.5l2.5 2.5H6.5L4 6.5z"></path><path d="M20 11.5H6.5L4 14h13.5l2.5-2.5z"></path><path d="M4 16.5h13.5l2.5 2.5H6.5L4 16.5z"></path></svg>';
            case 'arrow-up':
                return '<svg '.$attrs.'><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>';
            case 'arrow-down':
                return '<svg '.$attrs.'><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>';
            case 'wallet':
                return '<svg '.$attrs.'><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6"></path><circle cx="18" cy="14" r="1.5"></circle></svg>';
            case 'volume':
                return '<svg '.$attrs.'><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>';
            case 'x':
            case 'close':
                return '<svg '.$attrs.'><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
            default:
                return '<svg '.$attrs.'><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
        }
    }
}

if (!function_exists('system_avatars')) {
    function system_avatars(): array {
        return [
            [
                'id' => 'mascot_3d_glow',
                'name' => '3D Mascot Glow',
                'tag' => 'Official ✨',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="mBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#38bdf8"/><stop offset="60%" stop-color="#1e1b4b"/><stop offset="100%" stop-color="#090d16"/></radialGradient><linearGradient id="mBody" x1="20%" y1="0%" x2="80%" y2="100%"><stop offset="0%" stop-color="#ffffff"/><stop offset="60%" stop-color="#f1f5f9"/><stop offset="100%" stop-color="#cbd5e1"/></linearGradient><filter id="mGlow" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#00BAF2" flood-opacity="0.6"/></filter></defs><style>@keyframes mFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}@keyframes mTwinkle{0%,100%{opacity:.3;transform:scale(.8)}50%{opacity:1;transform:scale(1.2)}}.m-char{animation:mFloat 2.6s ease-in-out infinite;transform-origin:center}.m-star{animation:mTwinkle 1.8s ease-in-out infinite;transform-origin:center}</style><circle cx="50" cy="50" r="50" fill="url(#mBg)"/><circle cx="20" cy="24" r="1.8" fill="#38bdf8" class="m-star"/><circle cx="82" cy="28" r="2.2" fill="#e0f2fe" class="m-star" style="animation-delay:0.6s"/><circle cx="78" cy="74" r="1.5" fill="#38bdf8" class="m-star" style="animation-delay:1.2s"/><g class="m-char"><ellipse cx="50" cy="50" rx="32" ry="30" fill="url(#mBody)" filter="url(#mGlow)"/><ellipse cx="44" cy="46" rx="4.5" ry="8" fill="#0f172a"/><circle cx="45.5" cy="43" r="1.8" fill="#ffffff"/><ellipse cx="58" cy="46" rx="4.5" ry="8" fill="#0f172a"/><circle cx="59.5" cy="43" r="1.8" fill="#ffffff"/><path d="M46 56 Q51 61 56 56" stroke="#0f172a" stroke-width="2.5" fill="none" stroke-linecap="round"/><ellipse cx="38" cy="53" rx="3" ry="1.5" fill="#f472b6" opacity="0.6"/><ellipse cx="64" cy="53" rx="3" ry="1.5" fill="#f472b6" opacity="0.6"/></g></svg>'
            ],
            [
                'id' => 'streamer_live_3d',
                'name' => 'Cyber Streamer',
                'tag' => 'Twitch Live 💜',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="stBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#4c1d95"/><stop offset="100%" stop-color="#0f0728"/></radialGradient><linearGradient id="stCrown" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#a855f7"/><stop offset="100%" stop-color="#ec4899"/></linearGradient><linearGradient id="stFace" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" stop-color="#fed7aa"/><stop offset="100%" stop-color="#fba47e"/></linearGradient></defs><style>@keyframes stLiveGlow{0%,100%{filter:drop-shadow(0 0 2px #ec4899)}50%{filter:drop-shadow(0 0 8px #f43f5e)}}@keyframes stPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.03)}}.st-crown{animation:stLiveGlow 1.6s ease-in-out infinite}.st-avatar{animation:stPulse 3s ease-in-out infinite;transform-origin:50% 60%}</style><circle cx="50" cy="50" r="50" fill="url(#stBg)"/><g class="st-avatar"><circle cx="32" cy="30" r="11" fill="#4c1d95"/><circle cx="68" cy="30" r="11" fill="#4c1d95"/><path d="M26 48 C26 26, 74 26, 74 48" stroke="#ffffff" stroke-width="5" fill="none" stroke-linecap="round"/><g class="st-crown"><polygon points="36,22 42,12 50,18 58,12 64,22" fill="url(#stCrown)"/><rect x="36" y="20" width="28" height="6" rx="2" fill="#7e22ce"/><text x="50" y="25" font-size="4.2" font-weight="900" fill="#ffffff" text-anchor="middle" font-family="sans-serif">LIVE</text></g><ellipse cx="50" cy="56" rx="21" ry="23" fill="url(#stFace)"/><path d="M30 46 C36 40, 48 38, 50 42 C52 38, 64 40, 70 46 C66 38, 56 36, 50 36 C44 36, 34 38, 30 46 Z" fill="#3b0764"/><rect x="23" y="44" width="7" height="14" rx="3.5" fill="#f8fafc" stroke="#38bdf8" stroke-width="1.5"/><rect x="70" y="44" width="7" height="14" rx="3.5" fill="#f8fafc" stroke="#38bdf8" stroke-width="1.5"/><path d="M39 52 Q44 48 48 52" stroke="#1e293b" stroke-width="2.5" fill="none" stroke-linecap="round"/><circle cx="58" cy="51" r="4.5" fill="#0284c7"/><circle cx="59.5" cy="50" r="1.8" fill="#ffffff"/><ellipse cx="49" cy="56" rx="1.5" ry="1" fill="#ea580c"/><path d="M46 62 Q50 60 54 62" stroke="#1e293b" stroke-width="2" fill="none"/><path d="M47 62 Q50 70 53 62 Z" fill="#f43f5e"/></g></svg>'
            ],
            [
                'id' => 'ghostie_heart_3d',
                'name' => 'Ghostie Sweetheart',
                'tag' => 'Love & Sparkle 💖',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="ghBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#312e81"/><stop offset="100%" stop-color="#090514"/></radialGradient><linearGradient id="ghBody" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#ffffff"/><stop offset="80%" stop-color="#ede9fe"/><stop offset="100%" stop-color="#c4b5fd"/></linearGradient><linearGradient id="rainbowHeart" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#f43f5e"/><stop offset="40%" stop-color="#ec4899"/><stop offset="70%" stop-color="#8b5cf6"/><stop offset="100%" stop-color="#06b6d4"/></linearGradient></defs><style>@keyframes ghFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}@keyframes ghHeartPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.12)}}@keyframes ghMiniHeart{0%{transform:translateY(0) scale(.6);opacity:0}50%{opacity:.9}100%{transform:translateY(-16px) scale(1);opacity:0}}.gh-float{animation:ghFloat 2.8s ease-in-out infinite}.gh-heart{animation:ghHeartPulse 1.8s ease-in-out infinite;transform-origin:50% 64%}.gh-sparkle1{animation:ghMiniHeart 2.2s ease-in-out infinite}.gh-sparkle2{animation:ghMiniHeart 2.2s ease-in-out infinite 1.1s}</style><circle cx="50" cy="50" r="50" fill="url(#ghBg)"/><g class="gh-sparkle1" transform="translate(24, 45)"><path d="M0,0 C-2,-4 -8,-2 -8,2 C-8,6 0,11 0,11 C0,11 8,6 8,2 C8,-2 2,-4 0,0 Z" fill="#ec4899"/></g><g class="gh-sparkle2" transform="translate(74, 38)"><path d="M0,0 C-2,-3 -6,-1 -6,2 C-6,5 0,9 0,9 C0,9 6,5 6,2 C6,-1 2,-3 0,0 Z" fill="#38bdf8"/></g><g class="gh-float"><path d="M30 68 C25 42, 34 22, 50 22 C66 22, 75 42, 70 68 Q65 64, 60 68 Q55 72, 50 68 Q45 64, 40 68 Q35 72, 30 68 Z" fill="url(#ghBody)"/><ellipse cx="42" cy="42" rx="3.5" ry="4.5" fill="#312e81"/><circle cx="43.5" cy="40" r="1.5" fill="#ffffff"/><ellipse cx="58" cy="42" rx="3.5" ry="4.5" fill="#312e81"/><circle cx="59.5" cy="40" r="1.5" fill="#ffffff"/><path d="M47 48 Q50 52 53 48" stroke="#312e81" stroke-width="2" fill="none" stroke-linecap="round"/><ellipse cx="36" cy="47" rx="3" ry="1.5" fill="#f43f5e" opacity="0.6"/><ellipse cx="64" cy="47" rx="3" ry="1.5" fill="#f43f5e" opacity="0.6"/><g class="gh-heart"><path d="M50 56 C46 48, 32 50, 32 61 C32 70, 50 80, 50 80 C50 80, 68 70, 68 61 C68 50, 54 48, 50 56 Z" fill="url(#rainbowHeart)"/></g></g></svg>'
            ],
            [
                'id' => 'neon_cat_hacker_3d',
                'name' => 'Neon Cyber Hacker',
                'tag' => 'Cyberpunk ⚡',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="nhBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#18181b"/><stop offset="100%" stop-color="#020617"/></radialGradient><linearGradient id="nhVisor" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#06b6d4"/><stop offset="50%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#ec4899"/></linearGradient></defs><style>@keyframes nhVisorScan{0%,100%{transform:translateX(-16px)}50%{transform:translateX(16px)}}@keyframes nhEarGlow{0%,100%{filter:drop-shadow(0 0 3px #06b6d4)}50%{filter:drop-shadow(0 0 8px #ec4899)}}.nh-scan{animation:nhVisorScan 2s ease-in-out infinite}.nh-ears{animation:nhEarGlow 1.8s ease-in-out infinite}</style><circle cx="50" cy="50" r="50" fill="url(#nhBg)"/><g class="nh-ears"><polygon points="28,32 36,12 44,26" fill="#06b6d4"/><polygon points="31,30 36,16 41,26" fill="#ec4899"/><polygon points="72,32 64,12 56,26" fill="#06b6d4"/><polygon points="69,30 64,16 59,26" fill="#ec4899"/></g><path d="M26 44 C24 28, 38 22, 50 22 C62 22, 76 28, 74 44 L70 32 L62 36 L56 26 L50 34 L44 26 L38 36 L30 32 Z" fill="#7c3aed"/><ellipse cx="50" cy="56" rx="20" ry="22" fill="#fed7aa"/><rect x="30" y="46" width="40" height="11" rx="5" fill="url(#nhVisor)"/><g class="nh-scan"><rect x="47" y="47" width="6" height="9" rx="3" fill="#ffffff" opacity="0.85"/></g><rect x="22" y="44" width="7" height="15" rx="3.5" fill="#0f172a" stroke="#06b6d4" stroke-width="1.5"/><rect x="71" y="44" width="7" height="15" rx="3.5" fill="#0f172a" stroke="#ec4899" stroke-width="1.5"/><path d="M47 64 Q54 66 56 61" stroke="#1e293b" stroke-width="2" fill="none" stroke-linecap="round"/></svg>'
            ],
            [
                'id' => 'kpop_finger_heart_3d',
                'name' => 'Finger Heart Star',
                'tag' => 'K-Pop Vibe 🫰',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="kpBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#4c1d95"/><stop offset="100%" stop-color="#110726"/></radialGradient><linearGradient id="kpSleeve" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#ec4899"/><stop offset="100%" stop-color="#a855f7"/></linearGradient><linearGradient id="kpH1" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#f43f5e"/><stop offset="100%" stop-color="#fbbf24"/></linearGradient><linearGradient id="kpH2" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#a855f7"/><stop offset="100%" stop-color="#06b6d4"/></linearGradient></defs><style>@keyframes kpHeart1{0%{transform:translateY(0) scale(.6);opacity:0}40%{opacity:1;transform:translateY(-8px) scale(1)}100%{transform:translateY(-20px) scale(.7);opacity:0}}@keyframes kpHandSway{0%,100%{transform:rotate(0deg)}50%{transform:rotate(3deg)}}.kp-arm{animation:kpHandSway 3s ease-in-out infinite;transform-origin:30% 90%}.kp-h1{animation:kpHeart1 2.2s ease-in-out infinite}.kp-h2{animation:kpHeart1 2.2s ease-in-out infinite 0.7s}.kp-h3{animation:kpHeart1 2.2s ease-in-out infinite 1.4s}</style><circle cx="50" cy="50" r="50" fill="url(#kpBg)"/><g class="kp-h1" transform="translate(56, 32)"><path d="M0,0 C-2,-4 -7,-2 -7,2 C-7,6 0,11 0,11 C0,11 7,6 7,2 C7,-2 2,-4 0,0 Z" fill="url(#kpH1)"/></g><g class="kp-h2" transform="translate(68, 24)"><path d="M0,0 C-1.5,-3 -5,-1.5 -5,1.5 C-5,5 0,8 0,8 C0,8 5,5 5,1.5 C5,-1.5 1.5,-3 0,0 Z" fill="url(#kpH2)"/></g><g class="kp-h3" transform="translate(46, 26)"><path d="M0,0 C-1.5,-3 -4,-1.5 -4,1.5 C-4,4 0,7 0,7 C0,7 4,4 4,1.5 C4,-1.5 1.5,-3 0,0 Z" fill="#f43f5e"/></g><g class="kp-arm"><path d="M15 95 L46 64 C49 61, 54 61, 57 64 L65 72 C68 75, 68 80, 65 83 L34 114 Z" fill="url(#kpSleeve)"/><ellipse cx="58" cy="62" rx="9" ry="8" fill="#fbcfe8"/><ellipse cx="59" cy="52" rx="4.5" ry="8" fill="#fbcfe8" transform="rotate(15 59 52)"/><ellipse cx="64" cy="54" rx="4" ry="7" fill="#f472b6" transform="rotate(-25 64 54)"/></g></svg>'
            ],
            [
                'id' => 'vaporwave_dj_3d',
                'name' => 'Vaporwave Lo-Fi',
                'tag' => 'Chill Beats 🎧',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="vwBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#f43f5e"/><stop offset="60%" stop-color="#4c1d95"/><stop offset="100%" stop-color="#0f172a"/></radialGradient><linearGradient id="vwHat" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#f472b6"/><stop offset="100%" stop-color="#db2777"/></linearGradient></defs><style>@keyframes vwBob{0%,100%{transform:translateY(0)}50%{transform:translateY(4px)}}.vw-head{animation:vwBob 0.85s ease-in-out infinite alternate}</style><circle cx="50" cy="50" r="50" fill="url(#vwBg)"/><g class="vw-head"><rect x="26" y="44" width="6" height="24" rx="3" fill="#1e1b4b"/><rect x="33" y="46" width="6" height="26" rx="3" fill="#1e1b4b"/><rect x="61" y="46" width="6" height="26" rx="3" fill="#1e1b4b"/><rect x="68" y="44" width="6" height="24" rx="3" fill="#1e1b4b"/><ellipse cx="50" cy="58" rx="20" ry="22" fill="#78350f"/><ellipse cx="50" cy="68" rx="6" ry="5" fill="#3b0764"/><rect x="28" y="48" width="18" height="11" rx="4" fill="rgba(253, 224, 71, 0.8)" stroke="#ffffff" stroke-width="1.2"/><rect x="54" y="48" width="18" height="11" rx="4" fill="rgba(253, 224, 71, 0.8)" stroke="#ffffff" stroke-width="1.2"/><line x1="46" y1="53" x2="54" y2="53" stroke="#ffffff" stroke-width="1.5"/><circle cx="37" cy="53" r="2.5" fill="#1e1b4b"/><circle cx="63" cy="53" r="2.5" fill="#1e1b4b"/><ellipse cx="50" cy="38" rx="28" ry="12" fill="url(#vwHat)"/><path d="M30 38 C32 20, 68 20, 70 38 Z" fill="#ec4899"/><circle cx="42" cy="28" r="2" fill="#ffffff" opacity="0.8"/><circle cx="58" cy="28" r="2" fill="#ffffff" opacity="0.8"/><circle cx="50" cy="34" r="2" fill="#ffffff" opacity="0.8"/></g></svg>'
            ],
            [
                'id' => 'alchemist_potion_3d',
                'name' => 'Magic Potion Flask',
                'tag' => 'Wealth Elixir 🧪',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="ptBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#0f766e"/><stop offset="100%" stop-color="#042f2e"/></radialGradient><linearGradient id="ptLiquid" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#2dd4bf"/><stop offset="100%" stop-color="#059669"/></linearGradient><linearGradient id="ptCork" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#c084fc"/><stop offset="100%" stop-color="#9333ea"/></linearGradient></defs><style>@keyframes ptBubble{0%{transform:translateY(12px);opacity:0}50%{opacity:.9}100%{transform:translateY(-16px);opacity:0}}@keyframes ptFloat{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-4px) rotate(4deg)}}.pt-bottle{animation:ptFloat 3.2s ease-in-out infinite;transform-origin:center}.pt-b1{animation:ptBubble 1.8s ease-in-out infinite}.pt-b2{animation:ptBubble 1.8s ease-in-out infinite .9s}</style><circle cx="50" cy="50" r="50" fill="url(#ptBg)"/><g class="pt-bottle"><polygon points="44,24 56,24 54,32 46,32" fill="url(#ptCork)"/><rect x="42" y="30" width="16" height="5" rx="2" fill="#d8b4fe"/><path d="M46 35 L46 45 C32 50, 26 66, 32 78 C38 88, 62 88, 68 78 C74 66, 68 50, 54 45 L54 35 Z" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"/><path d="M30 68 C34 64, 46 68, 50 65 C54 62, 66 66, 70 68 C71 78, 62 86, 50 86 C38 86, 29 78, 30 68 Z" fill="url(#ptLiquid)"/><circle cx="44" cy="72" r="2.5" fill="#ffffff" opacity="0.8" class="pt-b1"/><circle cx="56" cy="76" r="3.2" fill="#ffffff" opacity="0.8" class="pt-b2"/><circle cx="50" cy="70" r="1.8" fill="#ffffff" opacity="0.6" class="pt-b1" style="animation-delay:0.4s"/><path d="M36 54 C32 62, 34 74, 38 78" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round" opacity="0.6"/></g></svg>'
            ],
            [
                'id' => 'cat_sunglasses_3d',
                'name' => 'Boss Cat with Shades',
                'tag' => 'Cool Swag 😎',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="ctBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#3b0764"/><stop offset="100%" stop-color="#18022a"/></radialGradient><linearGradient id="ctShades" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#ec4899"/><stop offset="100%" stop-color="#8b5cf6"/></linearGradient></defs><style>@keyframes ctGlint{0%,80%,100%{transform:translateX(-20px);opacity:0}20%,60%{opacity:.9}50%{transform:translateX(20px)}}@keyframes ctPop{0%,100%{transform:translateY(0)}50%{transform:translateY(-3px)}}.ct-bubble{animation:ctPop 2.8s ease-in-out infinite}.ct-lens-glint{animation:ctGlint 2.4s ease-in-out infinite}</style><circle cx="50" cy="50" r="50" fill="url(#ctBg)"/><g class="ct-bubble"><rect x="18" y="24" width="64" height="52" rx="14" fill="#ffffff" filter="drop-shadow(0 4px 10px rgba(0,0,0,0.3))"/><polygon points="32,76 40,76 26,88" fill="#ffffff"/><ellipse cx="50" cy="54" rx="20" ry="17" fill="#f97316"/><polygon points="34,42 42,28 48,39" fill="#f97316"/><polygon points="37,40 42,32 46,39" fill="#fbcfe8"/><polygon points="66,42 58,28 52,39" fill="#f97316"/><polygon points="63,40 58,32 54,39" fill="#fbcfe8"/><path d="M47 38 L50 42 L53 38" stroke="#c2410c" stroke-width="1.8" fill="none"/><g><rect x="33" y="47" width="16" height="11" rx="4" fill="url(#ctShades)"/><rect x="51" y="47" width="16" height="11" rx="4" fill="url(#ctShades)"/><line x1="48" y1="52" x2="52" y2="52" stroke="#1e293b" stroke-width="2"/><line x1="36" y1="48" x2="42" y2="56" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" class="ct-lens-glint"/></g><polygon points="48,60 52,60 50,62" fill="#fda4af"/><path d="M50 62 L50 65 Q47 66 45 64 M50 65 Q53 66 55 64" stroke="#1e293b" stroke-width="1.5" fill="none"/></g></svg>'
            ],
            [
                'id' => 'rgb_keycap_3d',
                'name' => 'RGB Mechanical Key',
                'tag' => 'Gamer Rig ⌨️',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="kbBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#1e1b4b"/><stop offset="100%" stop-color="#020617"/></radialGradient><linearGradient id="kbTop" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#f8fafc"/><stop offset="100%" stop-color="#cbd5e1"/></linearGradient></defs><style>@keyframes rgbCycle{0%{filter:hue-rotate(0deg)}100%{filter:hue-rotate(360deg)}}@keyframes kbPress{0%,100%{transform:translateY(0)}50%{transform:translateY(5px)}}.kb-rgb{animation:rgbCycle 3.5s linear infinite}.kb-switch{animation:kbPress 2.4s ease-in-out infinite}</style><circle cx="50" cy="50" r="50" fill="url(#kbBg)"/><g class="kb-rgb"><polygon points="50,66 84,48 50,30 16,48" fill="none" stroke="#ec4899" stroke-width="8" opacity="0.75" filter="drop-shadow(0 0 8px #06b6d4)"/></g><g class="kb-switch"><polygon points="18,46 50,64 50,80 18,62" fill="#475569"/><polygon points="50,64 82,46 82,62 50,80" fill="#334155"/><polygon points="50,28 80,44 50,60 20,44" fill="url(#kbTop)"/><polygon points="50,38 52,43 57,43 53,46 55,51 50,48 45,51 47,46 43,43 48,43" fill="#6366f1"/></g></svg>'
            ],
            [
                'id' => 'metaverse_gazer_3d',
                'name' => 'Metaverse Stargazer',
                'tag' => 'Future Vision 🌌',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="mgBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#312e81"/><stop offset="100%" stop-color="#020617"/></radialGradient></defs><style>@keyframes mgStars{0%,100%{opacity:.3;transform:scale(.8)}50%{opacity:1;transform:scale(1.3)}}@keyframes mgGaze{0%,100%{transform:translateY(0)}50%{transform:translateY(-3px)}}.mg-star{animation:mgStars 1.5s ease-in-out infinite;transform-origin:center}.mg-char{animation:mgGaze 3s ease-in-out infinite}</style><circle cx="50" cy="50" r="50" fill="url(#mgBg)"/><circle cx="22" cy="22" r="1.5" fill="#38bdf8" class="mg-star"/><circle cx="78" cy="20" r="2" fill="#ec4899" class="mg-star" style="animation-delay:0.6s"/><circle cx="84" cy="46" r="1.5" fill="#fef08a" class="mg-star" style="animation-delay:1.2s"/><g class="mg-char"><path d="M26 50 C26 28, 74 28, 74 50" stroke="#f8fafc" stroke-width="4.5" fill="none"/><rect x="23" y="46" width="7" height="14" rx="3.5" fill="#38bdf8"/><rect x="70" y="46" width="7" height="14" rx="3.5" fill="#f43f5e"/><ellipse cx="50" cy="36" rx="22" ry="12" fill="#0f172a"/><path d="M28 36 Q18 36 14 42 Q22 44 32 38" fill="#1e293b"/><ellipse cx="50" cy="56" rx="19" ry="21" fill="#fed7aa"/><circle cx="41" cy="51" r="7" fill="none" stroke="#f472b6" stroke-width="1.8"/><circle cx="59" cy="51" r="7" fill="none" stroke="#f472b6" stroke-width="1.8"/><line x1="48" y1="51" x2="52" y2="51" stroke="#f472b6" stroke-width="1.8"/><circle cx="41" cy="50" r="2.5" fill="#38bdf8"/><circle cx="59" cy="50" r="2.5" fill="#38bdf8"/><circle cx="42" cy="49" r="1" fill="#ffffff"/><circle cx="60" cy="49" r="1" fill="#ffffff"/><ellipse cx="50" cy="65" rx="3" ry="2" fill="#ea580c"/></g></svg>'
            ],
            [
                'id' => 'cyber_apple_3d',
                'name' => 'Cyber Apple Metallic',
                'tag' => 'Ultra Glass 🍏',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="apBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#1e1b4b"/><stop offset="100%" stop-color="#020617"/></radialGradient><linearGradient id="apAura" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#ec4899"/><stop offset="50%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#8b5cf6"/></linearGradient></defs><style>@keyframes apSpin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}@keyframes apFloat{0%,100%{transform:scale(1)}50%{transform:scale(1.05)}}.ap-aura{animation:apSpin 6s linear infinite;transform-origin:50% 50%}.ap-apple{animation:apFloat 2.8s ease-in-out infinite;transform-origin:50% 50%}</style><circle cx="50" cy="50" r="50" fill="url(#apBg)"/><circle cx="50" cy="50" r="42" fill="none" stroke="url(#apAura)" stroke-width="2.5" stroke-dasharray="8 6" class="ap-aura"/><g class="ap-apple" fill="#ffffff" filter="drop-shadow(0 4px 12px rgba(255,255,255,0.4))"><path d="M52 24 C56 18, 62 20, 62 20 C62 20, 60 27, 54 27 C52 27, 52 24, 52 24 Z"/><path d="M50 34 C44 28, 32 30, 32 46 C32 64, 46 76, 50 76 C54 76, 68 64, 68 46 C62 46, 60 41, 62 36 C56 34, 52 38, 50 34 Z"/></g></svg>'
            ],
            [
                'id' => 'golden_vip_whale_3d',
                'name' => 'Crypto Gold Whale',
                'tag' => 'VIP 1.0% Rate 💎',
                'is_animated' => true,
                'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="gwBg" cx="50%" cy="50%" r="50%"><stop offset="0%" stop-color="#1e3a8a"/><stop offset="100%" stop-color="#030712"/></radialGradient><linearGradient id="gwGold" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#fef08a"/><stop offset="50%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient></defs><style>@keyframes gwCoins{0%{transform:translateY(0);opacity:0}50%{opacity:1}100%{transform:translateY(-15px) rotate(180deg);opacity:0}}@keyframes gwPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.04)}}.gw-coin1{animation:gwCoins 2.2s ease-in-out infinite}.gw-coin2{animation:gwCoins 2.2s ease-in-out infinite 1.1s}.gw-whale{animation:gwPulse 2.8s ease-in-out infinite;transform-origin:center}</style><circle cx="50" cy="50" r="50" fill="url(#gwBg)"/><g class="gw-coin1" transform="translate(24, 40)"><circle cx="0" cy="0" r="3.5" fill="#fde047" stroke="#b45309" stroke-width="1"/><text x="0" y="2" font-size="3" font-weight="900" text-anchor="middle" fill="#78350f">₹</text></g><g class="gw-coin2" transform="translate(76, 36)"><circle cx="0" cy="0" r="3" fill="#fde047" stroke="#b45309" stroke-width="1"/><text x="0" y="2" font-size="2.6" font-weight="900" text-anchor="middle" fill="#78350f">₹</text></g><g class="gw-whale"><path d="M22 56 C22 40, 42 34, 64 40 C76 44, 82 54, 76 64 C68 74, 42 74, 28 66 Z" fill="url(#gwGold)"/><polygon points="22,56 12,46 16,58 12,68" fill="url(#gwGold)"/><path d="M48 34 Q50 20 54 28 M50 34 Q52 18 56 26" stroke="#38bdf8" stroke-width="2" fill="none" stroke-linecap="round"/><polygon points="56,36 62,26 68,34 74,26 78,36" fill="#38bdf8" stroke="#ffffff" stroke-width="1"/><circle cx="67" cy="50" r="3" fill="#0f172a"/><circle cx="68" cy="49" r="1.2" fill="#ffffff"/></g></svg>'
            ]
        ];
    }
}
?>

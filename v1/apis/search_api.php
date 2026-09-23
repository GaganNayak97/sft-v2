<?php
use App\Core\Auth;
use App\Core\Database;

header('Content-Type: application/json; charset=UTF-8');

$query = trim($_GET['q'] ?? '');

$routes = [
    ['title' => 'Home Overview', 'desc' => 'Balance overview, daily rate, recent activity', 'url' => 'index.php?page=home', 'icon' => 'home', 'tags' => 'home,dashboard,overview'],
    ['title' => 'Balance & Daily Interest', 'desc' => 'Deposit balance, daily increase ticker, compounding', 'url' => 'index.php?page=balance', 'icon' => 'trending', 'tags' => 'balance,interest,interest,daily,increase,compounding,ledger'],
    ['title' => 'Deposit Funds via UPI', 'desc' => 'Scan QR, copy UPI ID, submit UTR for instant credit', 'url' => 'index.php?page=deposit', 'icon' => 'deposit', 'tags' => 'deposit,add money,upi,qr,softpay,paytm,gpay,phonepe,utr'],
    ['title' => 'Withdraw Funds', 'desc' => '0.55% interest threshold eligibility & UPI/Bank payout', 'url' => 'index.php?page=withdraw', 'icon' => 'withdraw', 'tags' => 'withdraw,payout,cashout,0.55,eligibility'],
    ['title' => 'VIP Business Rewards', 'desc' => 'Upgrade to 1.00% daily interest on deposits ₹5,000+', 'url' => 'index.php?page=rewards', 'icon' => 'crown', 'tags' => 'rewards,vip,business,1 percent,rate,upgrade'],
    ['title' => 'Trading Terminal', 'desc' => 'Live candlestick trading with 95% payout on daily interest & demo', 'url' => 'index.php?page=trading', 'icon' => 'trending', 'tags' => 'trading,candlestick,chart,quotex,olymptrade,crypto,gold,forex,options,trade'],
    ['title' => 'How to design better UI 3.0', 'desc' => 'SoftPay theme guidelines & mobile layouts', 'url' => 'index.php?page=profile', 'icon' => 'settings', 'tags' => 'how to,ui,design,theme,better ui,ux'],
    ['title' => 'How to deposit funds via UPI', 'desc' => 'Scan QR or enter UTR for instant credit & 0.88% interest', 'url' => 'index.php?page=deposit', 'icon' => 'deposit', 'tags' => 'how to,deposit,add money,upi,compounding'],
    ['title' => 'How to withdraw daily earnings', 'desc' => '0.55% interest threshold eligibility & fast payout', 'url' => 'index.php?page=withdraw', 'icon' => 'withdraw', 'tags' => 'how to,withdraw,payout,cashout,eligibility'],
    ['title' => 'How to start Trading', 'desc' => 'Trade with demo or daily interest earnings at 95% payout', 'url' => 'index.php?page=trading', 'icon' => 'trending', 'tags' => 'how to,trade,trading,options,candlestick'],
    ['title' => 'How to earn 1.00% VIP interest', 'desc' => 'Upgrade to VIP tier on deposits ₹5,000+', 'url' => 'index.php?page=rewards', 'icon' => 'crown', 'tags' => 'how to,vip,rewards,1 percent,interest rate'],
    ['title' => 'How to sell USDT for cash', 'desc' => 'Convert USDT to INR directly at live rate', 'url' => 'index.php?page=usdt_sell', 'icon' => 'usdt', 'tags' => 'how to,sell usdt,crypto,usdt,rates'],
    ['title' => 'How to set PIN & Pattern lock', 'desc' => 'Configure 4-digit PIN or 3x3 touch pattern', 'url' => 'index.php?page=security', 'icon' => 'shield', 'tags' => 'how to,security,pin,pattern,lock'],
    ['title' => 'Sell USDT (Crypto Desk)', 'desc' => 'Convert USDT to INR directly at live rate', 'url' => 'index.php?page=usdt_sell', 'icon' => 'usdt', 'tags' => 'usdt,crypto,sell,usd,tether,trc20,inr,rates'],
    ['title' => 'Transactions', 'desc' => 'Full history of deposits, interest, withdrawals, and USDT liquidations', 'url' => 'index.php?page=history', 'icon' => 'history', 'tags' => 'history,passbook,transactions,records,logs,statement'],
    ['title' => 'Security PIN & Pattern Lock', 'desc' => 'Configure 4-digit PIN and 3x3 pattern lock', 'url' => 'index.php?page=security', 'icon' => 'shield', 'tags' => 'security,pin,pattern,lock,password'],
    ['title' => 'Account & Profile Settings', 'desc' => 'Edit profile details, UPI ID, theme mode', 'url' => 'index.php?page=profile', 'icon' => 'settings', 'tags' => 'profile,settings,theme,dark mode,upi'],
    ['title' => 'Help Center & Chatbot', 'desc' => 'Guides, FAQs, and 24/7 AI chat support', 'url' => 'index.php?page=help', 'icon' => 'chat', 'tags' => 'help,support,faq,chatbot,guide']
];

if (empty($query)) {
    echo json_encode(['success' => true, 'results' => array_slice($routes, 0, 6)]);
    exit;
}

$results = [];
$cleanQ = strtolower($query);

foreach ($routes as $r) {
    if (strpos(strtolower($r['title']), $cleanQ) !== false || 
        strpos(strtolower($r['desc']), $cleanQ) !== false || 
        strpos(strtolower($r['tags']), $cleanQ) !== false) {
        $results[] = $r;
    }
}

// Search user transactions if authenticated
if (Auth::check()) {
    $db = Database::getConnection();
    $stmt = $db->prepare("
        SELECT type, amount, description, reference_id, created_at 
        FROM transactions 
        WHERE user_id = ? AND (description LIKE ? OR reference_id LIKE ?)
        LIMIT 5
    ");
    $term = "%{$query}%";
    $stmt->execute([Auth::id(), $term, $term]);
    $txs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($txs as $t) {
        $results[] = [
            'title' => 'Tx: ' . $t['description'],
            'desc' => 'Ref: ' . $t['reference_id'] . ' • Amount: ₹' . number_format($t['amount'], 2),
            'url' => 'index.php?page=history',
            'icon' => 'history'
        ];
    }
}

echo json_encode(['success' => true, 'results' => $results]);
?>
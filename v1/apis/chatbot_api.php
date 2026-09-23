<?php
use App\Core\Auth;
use App\Core\Database;
use App\Core\InterestEngine;

header('Content-Type: application/json; charset=UTF-8');

$query = trim($_POST['query'] ?? $_GET['query'] ?? '');

if (empty($query)) {
    echo json_encode(['success' => false, 'answer' => 'Please ask a question!']);
    exit;
}

$db = Database::getConnection();
$cleanQuery = strtolower($query);

// 1. Keyword-based intelligent matching for banking rules
if (strpos($cleanQuery, '0.88') !== false || strpos($cleanQuery, 'interest') !== false || strpos($cleanQuery, 'interest rate') !== false) {
    $rates = InterestEngine::getRates();
    $answer = "📈 **Daily Interest (Interest) System:**\n\n" .
              "• **Standard Plan:** You earn **{$rates['base_rate']}% flat daily interest** on whatever amount you deposit.\n" .
              "• **Example:** If you deposit ₹10,000, you will get ₹" . (10000 * $rates['base_rate'] / 100) . " credited to your balance every single day at midnight!\n" .
              "• **VIP Upgrade:** Deposits of ₹5,000 or more automatically get upgraded to **{$rates['vip_rate']}% daily interest**.";
    echo json_encode(['success' => true, 'answer' => $answer]);
    exit;
}

if (strpos($cleanQuery, '0.55') !== false || strpos($cleanQuery, 'withdraw rule') !== false || strpos($cleanQuery, 'locked') !== false || (strpos($cleanQuery, 'withdraw') !== false && strpos($cleanQuery, 'eligib') !== false)) {
    $rates = InterestEngine::getRates();
    $answer = "🛡️ **The 0.55% Withdrawal Eligibility Rule:**\n\n" .
              "To maintain a sustainable high-yield savings ecosystem, a user can withdraw once their accumulated interest reaches at least **{$rates['withdraw_min_interest_pct']}% of their deposited principal**.\n\n" .
              "• **Example:** If you deposit ₹1,000, you only need ₹5.50 in earned interest to unlock withdrawals.\n" .
              "• At 0.88% daily return, this requirement is usually met in **less than 1 single day**!";
    echo json_encode(['success' => true, 'answer' => $answer]);
    exit;
}

if (strpos($cleanQuery, 'deposit') !== false || strpos($cleanQuery, 'add money') !== false || strpos($cleanQuery, 'upi') !== false) {
    $vpa = app_config('app.upi_vpa', 'softpay@upi');
    $answer = "💳 **How to Deposit via UPI:**\n\n" .
              "1. Go to the **Deposit** page.\n" .
              "2. Choose your amount (Min ₹100).\n" .
              "3. Scan the SoftPay / UPI QR code or copy the UPI VPA (`{$vpa}`).\n" .
              "4. Complete payment on Paytm, PhonePe, or GPay.\n" .
              "5. Copy the 12-digit UTR from your UPI app and paste it in the form. Balance is credited instantly!";
    echo json_encode(['success' => true, 'answer' => $answer]);
    exit;
}

if (strpos($cleanQuery, 'vip') !== false || strpos($cleanQuery, 'reward') !== false || strpos($cleanQuery, '1%') !== false) {
    $rates = InterestEngine::getRates();
    $answer = "👑 **Business VIP Reward Tier:**\n\n" .
              "Any user with total active deposits of **₹" . number_format($rates['vip_threshold']) . " or higher** is automatically upgraded to Business VIP.\n\n" .
              "• Your daily interest increases from 0.88% to **{$rates['vip_rate']}% per day** (30% per month)!\n" .
              "• You receive priority withdrawal approvals and dedicated VIP support.";
    echo json_encode(['success' => true, 'answer' => $answer]);
    exit;
}

if (strpos($cleanQuery, 'usdt') !== false || strpos($cleanQuery, 'crypto') !== false || strpos($cleanQuery, 'sell usd') !== false) {
    $rates = InterestEngine::getRates();
    $answer = "🌐 **USDT Direct Sell Desk:**\n\n" .
              "We have built a dedicated crypto liquidation portal! You can sell USDT directly for Indian Rupees (INR) at the live guaranteed rate of **1 USDT = ₹" . number_format($rates['usdt_rate'], 2) . "**.\n\n" .
              "Navigate to the **Sell USDT** page in your sidebar to initiate an order.";
    echo json_encode(['success' => true, 'answer' => $answer]);
    exit;
}

if (strpos($cleanQuery, 'pin') !== false || strpos($cleanQuery, 'pattern') !== false || strpos($cleanQuery, 'security') !== false) {
    $answer = "🔒 **PIN & Pattern Lock Security:**\n\n" .
              "You can protect your wallet with two security layers:\n" .
              "1. **4-Digit PIN:** Set via Settings > Security.\n" .
              "2. **3x3 Pattern Lock:** Connect dots on our interactive canvas.\n\n" .
              "Both can be used to authorize withdrawals and protect unauthorized access.";
    echo json_encode(['success' => true, 'answer' => $answer]);
    exit;
}

if (strpos($cleanQuery, 'invite') !== false || strpos($cleanQuery, 'friend') !== false || strpos($cleanQuery, 'refer') !== false) {
    $answer = "🎁 **Invite & Earn Program:**\n\n" .
              "Share your unique referral code or link from the Home page or Profile.\n" .
              "When your invited friend deposits money, you instantly receive a **5% direct commission** into your account balance!";
    echo json_encode(['success' => true, 'answer' => $answer]);
    exit;
}

// 2. Search Database support_faqs
$stmtFaq = $db->prepare("
    SELECT answer FROM support_faqs 
    WHERE question LIKE ? OR tags LIKE ? 
    LIMIT 1
");
$term = "%{$query}%";
$stmtFaq->execute([$term, $term]);
$faqAnswer = $stmtFaq->fetchColumn();

if ($faqAnswer) {
    echo json_encode(['success' => true, 'answer' => $faqAnswer]);
    exit;
}

// Default helpful fallback
$answer = "I'm here to help! You can ask about:\n" .
          "• How the **0.88% daily interest** works\n" .
          "• Why withdrawals have a **0.55% interest requirement**\n" .
          "• How to deposit via **UPI QR / UTR**\n" .
          "• How to upgrade to the **1.00% VIP Business Tier**\n" .
          "• How to configure your **PIN or Pattern lock**\n" .
          "• How to **Sell USDT** for instant INR\n\n" .
          "What would you like to know more about?";

echo json_encode(['success' => true, 'answer' => $answer]);
?>
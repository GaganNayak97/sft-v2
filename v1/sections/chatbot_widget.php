<!-- Chatbot Floating Launcher -->
<div class="chatbot-launcher" id="chatbotLauncher" title="SoftPay Smart Assistant">
  <img src="<?php echo asset('images/logo.png'); ?>" alt="Chat" style="width: 34px; height: 34px; border-radius: 9px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.25);">
</div>

<!-- Chatbot Window -->
<div class="chatbot-window" id="chatbotWindow">
  <div class="chatbot-header">
    <div class="d-flex align-items-center gap-2">
      <img src="<?php echo asset('images/logo.png'); ?>" alt="Mascot" style="width: 28px; height: 28px; border-radius: 7px; object-fit: cover; border: 1.5px solid rgba(255,255,255,0.4);">
      <div>
        <strong style="font-size: 0.9rem; display: block; line-height: 1.2;">SoftPay Assistant</strong>
        <span style="font-size: 0.7rem; opacity: 0.9; display: flex; align-items: center; gap: 4px;">
          <span style="width: 7px; height: 7px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
          Online &bull; 24/7 AI Helper
        </span>
      </div>
    </div>
    <button type="button" class="icon-btn" id="closeChatbotBtn" style="background: transparent; border: none; color: #fff; width: 28px; height: 28px;">✕</button>
  </div>

  <div class="chatbot-messages" id="chatbotMessages">
    <div class="chat-bubble bot">
      Hello! 👋 Welcome to SoftPay. I am your 24/7 verified banking assistant. Ask me anything about deposits, daily 0.88% interest, 0.55% withdrawal rule, or VIP rewards!
    </div>
    
    <!-- Quick Suggestion FAQ Chips -->
    <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px;" id="chatFaqChips">
      <button type="button" class="preset-chip chat-chip" data-query="How does 0.88% daily interest work?">0.88% Interest?</button>
      <button type="button" class="preset-chip chat-chip" data-query="Why is withdrawal locked (0.55% rule)?">0.55% Withdraw Rule?</button>
      <button type="button" class="preset-chip chat-chip" data-query="How to deposit money via UPI?">Deposit UPI?</button>
      <button type="button" class="preset-chip chat-chip" data-query="How to get 1% VIP rate?">VIP 1.0% Plan?</button>
    </div>
  </div>

  <form class="chatbot-input-bar" id="chatbotForm">
    <input type="text" id="chatInputText" class="chat-input" placeholder="Type your question..." autocomplete="off">
    <button type="submit" class="btn btn-sm btn-primary" style="border-radius: var(--border-radius-full); padding: 6px 14px;">
      Send
    </button>
  </form>
</div>

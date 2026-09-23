/**
 * SoftPay Assistant - Interactive AI Chatbot Controller
 */

$(document).ready(function() {
  const launcher = $('#chatbotLauncher');
  const windowElem = $('#chatbotWindow');
  const messages = $('#chatbotMessages');
  const form = $('#chatbotForm');
  const input = $('#chatInputText');

  function openChatbot() {
    windowElem.addClass('open');
    $('body').addClass('chatbot-open');
    setTimeout(() => input.focus(), 80);
  }

  function closeChatbot() {
    windowElem.removeClass('open');
    $('body').removeClass('chatbot-open');
  }

  launcher.on('click', function() {
    if (windowElem.hasClass('open')) {
      closeChatbot();
    } else {
      openChatbot();
    }
  });

  $('#closeChatbotBtn').on('click', function() {
    closeChatbot();
  });

  $('#openChatbotFromHelpBtn').on('click', function() {
    openChatbot();
  });

  function appendMessage(text, sender) {
    const bubble = $('<div></div>')
      .addClass('chat-bubble ' + sender)
      .html(text.replace(/\n/g, '<br>'));
    messages.append(bubble);
    messages.scrollTop(messages[0].scrollHeight);
  }

  function sendQuery(queryText) {
    if (!queryText.trim()) return;

    appendMessage(queryText, 'user');
    input.val('');

    // Typing indicator
    const typingBubble = $('<div class="chat-bubble bot">Thinking... 💬</div>');
    messages.append(typingBubble);
    messages.scrollTop(messages[0].scrollHeight);

    $.ajax({
      url: 'index.php?api=chatbot',
      type: 'POST',
      data: { query: queryText },
      dataType: 'json',
      success: function(res) {
        typingBubble.remove();
        appendMessage(res.answer || 'I could not process that. Please ask again.', 'bot');
      },
      error: function() {
        typingBubble.remove();
        appendMessage('Sorry, I encountered a temporary connection issue. Please try again.', 'bot');
      }
    });
  }

  form.on('submit', function(e) {
    e.preventDefault();
    sendQuery(input.val());
  });

  // Suggestion chip clicks
  $(document).on('click', '.chat-chip', function() {
    const query = $(this).data('query');
    sendQuery(query);
  });

  // Close chatbot on Escape key
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && windowElem.hasClass('open')) {
      closeChatbot();
    }
  });
});

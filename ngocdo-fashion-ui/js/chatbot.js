// ===== AI Chatbot Widget - Standalone UI =====
const chatbotFab = document.getElementById('chatbotFab');
const chatbotWindow = document.getElementById('chatbotWindow');
const chatbotClose = document.getElementById('chatbotClose');
const chatbotForm = document.getElementById('chatbotForm');
const chatbotInput = document.getElementById('chatbotInput');
const chatbotMessages = document.getElementById('chatbotMessages');

// Toggle chatbot
chatbotFab?.addEventListener('click', () => {
  chatbotWindow?.classList.toggle('open');
  chatbotFab?.classList.toggle('hidden');
  if (chatbotWindow?.classList.contains('open')) {
    chatbotInput?.focus();
  }
});

chatbotClose?.addEventListener('click', () => {
  chatbotWindow?.classList.remove('open');
  chatbotFab?.classList.remove('hidden');
});

// Send message (Mock)
chatbotForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const msg = chatbotInput.value.trim();
  if (!msg) return;

  appendMessage('user', msg);
  chatbotInput.value = '';

  // Show typing indicator
  const typingEl = appendMessage('bot', '<div class="typing-dots"><span></span><span></span><span></span></div>');

  // Mock Delay
  setTimeout(() => {
    typingEl.remove();
    let reply = "Đây là phiên bản giao diện (UI Only). Chức năng AI Chatbot thật sẽ hoạt động khi kết nối với server!";
    
    if (msg.toLowerCase().includes('áo') || msg.toLowerCase().includes('polo')) {
        reply = "Tôi thấy bạn đang tìm áo. Chúng tôi có các mẫu **Áo Polo Premium** và **Áo Sơ Mi Linen** rất đẹp!";
    } else if (msg.toLowerCase().includes('giá') || msg.toLowerCase().includes('bao nhiêu')) {
        reply = "Giá sản phẩm dao động từ **300.000₫** đến **900.000₫**. Bạn có thể xem chi tiết trong danh mục!";
    }

    appendMessage('bot', reply.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'));
  }, 1000);
});

function appendMessage(who, html) {
  const div = document.createElement('div');
  div.className = `chat-msg ${who}`;
  div.innerHTML = `<div class="chat-bubble">${html}</div>`;
  chatbotMessages?.appendChild(div);
  if (chatbotMessages) chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
  return div;
}

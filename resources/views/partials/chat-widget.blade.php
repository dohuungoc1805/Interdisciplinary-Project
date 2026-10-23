<div id="chat-box" class="nd-chat-panel z-[151] hidden max-h-[min(420px,70vh)] w-[min(360px,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border border-[color:var(--line-soft)] bg-white shadow-2xl" style="height: 400px">
    <div class="flex items-center justify-between border-b border-white/10 bg-gradient-to-r from-[#1a1a2e] to-[#16213e] px-4 py-3 text-white">
        <span class="text-sm font-semibold"><i class="fas fa-robot mr-2 text-[color:var(--gold)]"></i>Trợ lý cửa hàng</span>
        <div class="flex items-center gap-3">
            <button type="button" id="chat-clear" class="text-xs text-white/65 transition hover:text-white" title="Xóa lịch sử chat" aria-label="Xóa lịch sử chat"><i class="fas fa-trash-alt"></i></button>
            <button type="button" id="chat-close" class="text-white/70 transition hover:text-white" aria-label="Đóng">&times;</button>
        </div>
    </div>
    <div id="chat-messages" class="flex-1 space-y-2 overflow-y-auto bg-[#f7f7f9] p-3 text-sm"></div>
    <form id="chat-form" class="flex gap-2 border-t border-[color:var(--line-soft)] bg-white p-2">
        @csrf
        <input id="chat-input" class="field-control flex-1 py-2 text-sm" type="text" placeholder="Nhập tin nhắn..." autocomplete="off" />
        <button class="rounded-full bg-[color:var(--accent)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[color:var(--accent-hover)]" type="submit">Gửi</button>
    </form>
</div>
<button type="button" id="chat-fab" class="nd-chat-fab !bottom-24 sm:!bottom-6" aria-label="Mở chat"><i class="fas fa-comments"></i></button>
<script>
const chatEscapeHtml = (value) => value
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#039;');

const chatLinkAttributes = (href) => {
  try {
    const url = new URL(href.replace(/&amp;/g, '&'), window.location.origin);
    if (url.origin === window.location.origin) {
      return 'class="text-[color:var(--accent)] underline break-all hover:text-[color:var(--accent-hover)]"';
    }
  } catch (e) {}

  return 'target="_blank" rel="noopener noreferrer" class="text-[color:var(--accent)] underline break-all hover:text-[color:var(--accent-hover)]"';
};

const chatAnchor = (href, label) => `<a href="${href}" ${chatLinkAttributes(href)}>${label}</a>`;

const chatLinkifyPlainUrls = (value) => value.replace(/https?:\/\/[^\s<]+/g, (rawUrl) => {
  const trailing = rawUrl.match(/[.,!?;:)]+$/)?.[0] || '';
  const href = rawUrl.slice(0, rawUrl.length - trailing.length);

  return chatAnchor(href, href) + trailing;
});

const chatFormatMessage = (value) => {
  const escaped = chatEscapeHtml(value || '');
  return escaped
    .replace(/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/g, (_, label, href) => chatAnchor(href, label))
    .replace(/(^|[\s>])((?:https?:\/\/)[^\s<]+)/g, (match, prefix, rawUrl) => prefix + chatLinkifyPlainUrls(rawUrl))
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\n/g, '<br>');
};

const chatHistoryKey = 'qudena_chat_history_v1';
const chatIsAuthenticated = @json(auth()->check());
const chatBox = document.getElementById('chat-messages');
const chatToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';

const chatAppend = (role, msg) => {
  const d = document.createElement('div');
  d.className = role === 'user' ? 'text-right' : 'text-left';
  d.innerHTML = '<span class="inline-block max-w-[85%] rounded-2xl px-3 py-2 '+(role === 'user' ? 'bg-[color:var(--accent)] text-white' : 'border border-[color:var(--line-soft)] bg-white text-[color:var(--text-main)]')+'">'+chatFormatMessage(msg)+'</span>';
  chatBox?.appendChild(d);
  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
};

const chatSaveGuestHistory = (messages) => {
  if (!chatIsAuthenticated) localStorage.setItem(chatHistoryKey, JSON.stringify(messages.slice(-100)));
};

const chatReadGuestHistory = () => {
  try {
    const history = JSON.parse(localStorage.getItem(chatHistoryKey) || '[]');
    return Array.isArray(history) ? history : [];
  } catch (error) {
    return [];
  }
};

const chatRenderHistory = (messages) => {
  if (!chatBox) return;
  chatBox.replaceChildren();
  messages.forEach((item) => chatAppend(item.role, item.message));
};

const chatLoadHistory = async () => {
  if (chatIsAuthenticated) {
    try {
      const response = await fetch('{{ route('chat.history') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      const payload = await response.json();
      chatRenderHistory(payload.messages || []);
      return;
    } catch (error) {}
  }
  chatRenderHistory(chatReadGuestHistory());
};

document.getElementById('chat-fab')?.addEventListener('click', () => {
  document.getElementById('chat-box').classList.remove('hidden');
  document.getElementById('chat-box').classList.add('flex');
});
document.getElementById('chat-close')?.addEventListener('click', () => {
  document.getElementById('chat-box').classList.add('hidden');
  document.getElementById('chat-box').classList.remove('flex');
});
document.getElementById('chat-clear')?.addEventListener('click', async () => {
  if (! window.confirm('Xóa toàn bộ lịch sử trò chuyện?')) return;
  if (chatIsAuthenticated) {
    await fetch('{{ route('chat.history.clear') }}', { method: 'DELETE', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': chatToken } });
  } else {
    localStorage.removeItem(chatHistoryKey);
  }
  chatRenderHistory([]);
});

document.getElementById('chat-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const input = document.getElementById('chat-input');
  const text = (input.value || '').trim();
  if (!text) return;
  const submit = e.currentTarget.querySelector('button[type="submit"]');
  const history = chatIsAuthenticated ? [] : chatReadGuestHistory();
  chatAppend('user', text);
  if (!chatIsAuthenticated) history.push({ role: 'user', message: text });
  chatSaveGuestHistory(history);
  input.value = '';
  if (submit) submit.disabled = true;
  try {
    const requestBody = { message: text };
    if (!chatIsAuthenticated) requestBody.history = history.slice(-10);
    const res = await fetch('{{ url('/chat') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': chatToken, 'Accept': 'application/json' }, body: JSON.stringify(requestBody) });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Chat request failed.');
    chatAppend('assistant', data.reply || 'Không có phản hồi.');
    if (!chatIsAuthenticated) {
      history.push({ role: 'assistant', message: data.reply || 'Không có phản hồi.' });
      chatSaveGuestHistory(history);
    }
  } catch (err) {
    const fallback = 'Lỗi kết nối, thử lại sau.';
    chatAppend('assistant', fallback);
    if (!chatIsAuthenticated) {
      history.push({ role: 'assistant', message: fallback });
      chatSaveGuestHistory(history);
    }
  } finally {
    if (submit) submit.disabled = false;
  }
});

chatLoadHistory();
</script>

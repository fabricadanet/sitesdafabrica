const iframe = document.getElementById('editorFrame');
let iframeDoc = null;
let currentTemplate = TEMPLATE_NAME || 'institucional';
let currentHTML = '';
let projectLoaded = false;

// =========================
// Inicialização
// =========================
window.addEventListener('DOMContentLoaded', () => {
  loadTemplate(currentTemplate);
});

// =========================
// Carregar Template / Projeto
// =========================
async function loadTemplate(name) {
  iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

  if (PROJECT_ID) {
    try {
      const res = await fetch(`/projects/get?id=${PROJECT_ID}`);
      const json = await res.json();
      if (json.success && json.data && json.data.content_html) {
        renderTemplate(json.data.content_html);
        applyGlobalVars(json.data.global_vars);
        return;
      }
    } catch (err) { console.warn('Falha ao carregar projeto:', err); }
  }

  // Carrega template base
  const res = await fetch(`/templates/${name}.html`);
  const html = await res.text();
  renderTemplate(html);
}


// ==============================
// Renderizar conteúdo + montar painel rapidamente
// ==============================
function renderTemplate(html) {
  iframeDoc.open();
  iframeDoc.write(html);
  iframeDoc.close();

  currentHTML = html;

  // Primeira tentativa imediata
  tryBuildSidebar(0);
}

// tenta montar o painel várias vezes até o DOM estar completo
function tryBuildSidebar(attempt) {
  if (attempt > 10) return console.warn("❌ Timeout ao inicializar painel lateral.");

  // Evita erro caso o iframe ainda não tenha carregado
  try {
    const ready = iframeDoc && iframeDoc.body && iframeDoc.body.querySelectorAll('[data-edit]').length > 0;
    if (ready) {
      buildSidebar();
      console.log(`✅ Painel lateral inicializado (tentativa ${attempt + 1})`);
      return;
    }
  } catch (e) {
    console.warn("Iframe ainda carregando...", e);
  }

  // tenta novamente a cada 150ms até detectar o DOM
  setTimeout(() => tryBuildSidebar(attempt + 1), 150);
}

// =========================
// Painel lateral dinâmico
// =========================
// ==============================
//  Painel lateral dinâmico (buildSidebar v2)
// ==============================
function buildSidebar() {
  console.log('🧱 Montando painel lateral...');

  const varsContainer = document.getElementById('vars-container');
  const textsContainer = document.getElementById('texts-container');
  const imagesContainer = document.getElementById('images-container');

  // Feedback visual durante o carregamento
  varsContainer.innerHTML = '<div class="text-muted small">⏳ Carregando variáveis...</div>';
  textsContainer.innerHTML = '<div class="text-muted small">⏳ Carregando textos...</div>';
  imagesContainer.innerHTML = '<div class="text-muted small">⏳ Carregando imagens...</div>';

  // Espera o DOM do iframe estar disponível
  if (!iframeDoc || !iframeDoc.body) {
    console.warn('⚠️ iframe ainda não está pronto. Tentando novamente...');
    setTimeout(buildSidebar, 150);
    return;
  }

  // ==============================
  // 🎨 VARIÁVEIS GLOBAIS (CSS)
  // ==============================
  const styles = getComputedStyle(iframeDoc.documentElement);
  const vars = [...styles]
    .filter(n => n.startsWith('--'))
    .map(n => ({ name: n, value: styles.getPropertyValue(n).trim() }));

  varsContainer.innerHTML = '';
  if (vars.length === 0) {
    varsContainer.innerHTML = '<div class="text-muted small">Nenhuma variável global encontrada.</div>';
  } else {
    vars.forEach(v => {
      const label = document.createElement('label');
      label.textContent = v.name;
      label.className = 'form-label text-muted small mt-2';

      const input = document.createElement('input');
      input.type = v.value.match(/^#|rgb|hsl/) ? 'color' : 'text';
      input.value = v.value;
      input.className = 'form-control mb-2';
      input.oninput = () => {
        iframeDoc.documentElement.style.setProperty(v.name, input.value);
        saveDraft();
      };

      varsContainer.appendChild(label);
      varsContainer.appendChild(input);
    });
  }

  // ==============================
  // 🖋️ TEXTOS EDITÁVEIS
  // ==============================
  const textEls = iframeDoc.querySelectorAll('[data-edit]:not(img)');
  textsContainer.innerHTML = '';
  if (textEls.length === 0) {
    textsContainer.innerHTML = '<div class="text-muted small">Nenhum texto encontrado.</div>';
  } else {
    textEls.forEach(el => {
      const name = el.dataset.edit || 'sem-nome';
      const label = document.createElement('label');
      label.textContent = name;
      label.className = 'form-label text-muted small mt-2';

      const textarea = document.createElement('textarea');
      textarea.className = 'form-control mb-2';
      textarea.value = el.innerText.trim();
      textarea.rows = 2;
      textarea.oninput = () => {
        el.innerText = textarea.value;
        saveDraft();
      };

      textsContainer.appendChild(label);
      textsContainer.appendChild(textarea);
    });
  }

  // ==============================
  // 🖼️ IMAGENS EDITÁVEIS
  // ==============================
  const imgEls = iframeDoc.querySelectorAll('img[data-edit]');
  imagesContainer.innerHTML = '';
  if (imgEls.length === 0) {
    imagesContainer.innerHTML = '<div class="text-muted small">Nenhuma imagem encontrada.</div>';
  } else {
    imgEls.forEach(img => {
      const name = img.dataset.edit || 'imagem-sem-id';
      const label = document.createElement('label');
      label.textContent = name;
      label.className = 'form-label text-muted small mt-2';

      const preview = document.createElement('img');
      preview.src = img.src;
      preview.style.width = '100%';
      preview.style.borderRadius = '6px';
      preview.style.marginBottom = '6px';

      const input = document.createElement('input');
      input.type = 'file';
      input.accept = 'image/*';
      input.className = 'form-control mb-3';
      input.onchange = e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
          img.src = ev.target.result;
          preview.src = ev.target.result;
          saveDraft();
        };
        reader.readAsDataURL(file);
      };

      imagesContainer.appendChild(label);
      imagesContainer.appendChild(preview);
      imagesContainer.appendChild(input);
    });
  }

  console.log(`✅ Painel montado: ${vars.length} variáveis, ${textEls.length} textos, ${imgEls.length} imagens.`);
}


// helper
function labelWrap(name, el) {
  const div = document.createElement('div');
  div.innerHTML = `<label class="form-label text-muted small">${name}</label>`;
  div.appendChild(el);
  return div;
}

// =========================
// Aplicar variáveis globais
// =========================
function applyGlobalVars(varsJSON) {
  if (!varsJSON) return;
  try {
    const vars = JSON.parse(varsJSON);
    for (const [key, val] of Object.entries(vars)) {
      iframeDoc.documentElement.style.setProperty(key, val);
    }
  } catch (e) { console.error('Erro aplicando variáveis globais', e); }
}

// =========================
// Salvamento local (rascunho)
// =========================
function saveDraft() {
  currentHTML = iframeDoc.documentElement.outerHTML;
  localStorage.setItem('draftHTML', currentHTML);
}

// =========================
// Salvar Projeto
// =========================
document.getElementById('saveProject').onclick = async () => {
  const title = iframeDoc.title || 'Sem título';
  const contentHtml = iframeDoc.documentElement.outerHTML;

  const cssVars = {};
  const styles = getComputedStyle(iframeDoc.documentElement);
  [...styles].forEach(n => {
    if (n.startsWith('--')) cssVars[n] = styles.getPropertyValue(n).trim();
  });

  const form = new FormData();
  form.append('id', PROJECT_ID || '');
  form.append('title', title);
  form.append('template', currentTemplate);
  form.append('content_html', contentHtml);
  form.append('global_vars', JSON.stringify(cssVars));

  const res = await fetch('/projects/save', { method: 'POST', body: form });
  const data = await res.json();
  if (data.success) {
    alert('💾 Projeto salvo!');
    if (!PROJECT_ID && data.id) window.location.href = `/editor?id=${data.id}&template=${currentTemplate}`;
  } else alert('Erro ao salvar: ' + (data.message || 'Falha desconhecida'));
};

// =========================
// Preview / Download
// =========================
document.getElementById('preview').onclick = () => {
  const blob = new Blob([iframeDoc.documentElement.outerHTML], { type: 'text/html' });
  const url = URL.createObjectURL(blob);
  window.open(url, '_blank');
};

document.getElementById('downloadSite').onclick = async () => {
  const zip = new JSZip();
  zip.file('index.html', iframeDoc.documentElement.outerHTML);
  const blob = await zip.generateAsync({ type: 'blob' });
  saveAs(blob, `${currentTemplate}.zip`);
};

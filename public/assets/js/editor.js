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

// =========================
// Renderização e DOM
// =========================
function renderTemplate(html) {
  iframeDoc.open();
  iframeDoc.write(html);
  iframeDoc.close();
  iframe.onload = () => {
    currentHTML = html;
    buildSidebar(); // monta o painel lateral
  };
}

// =========================
// Painel lateral dinâmico
// =========================
function buildSidebar() {
  const varsContainer = document.getElementById('vars-container');
  const textsContainer = document.getElementById('texts-container');
  const imagesContainer = document.getElementById('images-container');
  varsContainer.innerHTML = '';
  textsContainer.innerHTML = '';
  imagesContainer.innerHTML = '';

  // ========== Variáveis globais CSS ==========
  const styles = getComputedStyle(iframeDoc.documentElement);
  const vars = [...styles]
    .filter(n => n.startsWith('--'))
    .map(n => ({ name: n, value: styles.getPropertyValue(n).trim() }));

  vars.forEach(v => {
    const input = document.createElement('input');
    input.type = v.value.startsWith('#') || v.name.includes('cor') ? 'color' : 'text';
    input.value = v.value;
    input.className = 'form-control mb-2';
    input.title = v.name;
    input.oninput = () => {
      iframeDoc.documentElement.style.setProperty(v.name, input.value);
      saveDraft();
    };
    varsContainer.appendChild(labelWrap(v.name, input));
  });

  // ========== Textos editáveis ==========
  const textEls = iframeDoc.querySelectorAll('[data-edit]:not(img)');
  textEls.forEach(el => {
    const name = el.dataset.edit;
    const textarea = document.createElement('textarea');
    textarea.className = 'form-control mb-2';
    textarea.value = el.innerText.trim();
    textarea.rows = 2;
    textarea.oninput = () => {
      el.innerText = textarea.value;
      saveDraft();
    };
    textsContainer.appendChild(labelWrap(name, textarea));
  });

  // ========== Imagens ==========
  const imgEls = iframeDoc.querySelectorAll('img[data-edit]');
  imgEls.forEach(img => {
    const name = img.dataset.edit;
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.className = 'form-control mb-2';
    input.onchange = e => {
      const file = e.target.files[0];
      const reader = new FileReader();
      reader.onload = ev => {
        img.src = ev.target.result;
        saveDraft();
      };
      reader.readAsDataURL(file);
    };
    imagesContainer.appendChild(labelWrap(name, input));
  });
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

const iframe = document.getElementById('editorFrame');
let iframeDoc = null;
let currentHTML = '';
let currentTemplate = TEMPLATE_NAME || 'institucional';

// ========== CARREGAR TEMPLATE ==========
async function loadTemplate(name = currentTemplate) {
  if (PROJECT_ID) {
    const res = await fetch(`/projects/get?id=${PROJECT_ID}`);
    const data = await res.json();
    if (data.success && data.data.content_html) {
      renderTemplate(data.data.content_html);

      // 🔹 Restaurar variáveis globais
      if (data.data.global_vars) {
        const vars = JSON.parse(data.data.global_vars);
        for (const [name, value] of Object.entries(vars)) {
          iframeDoc.documentElement.style.setProperty(name, value);
        }
      }

      return;
    }
  }

  if (!PROJECT_ID || !data?.data?.content_html) {
    try {
      const res = await fetch(`/templates/${name}.html`);
      const html = await res.text();
      renderTemplate(html);
    } catch (e) {
      console.error('Erro ao carregar template:', e);
      renderTemplate('<h1>Erro ao carregar template.</h1>');
    }
  } else {
    const saved = localStorage.getItem('draftHTML');
    renderTemplate(saved || defaultTemplate);
  }

}


// ========== RENDERIZAR ==========
function renderTemplate(html) {
  iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
  iframeDoc.open();
  iframeDoc.write(html);
  iframeDoc.close();
  currentHTML = html;
  generateSidebar();
}

// ========== GERAR SIDEBAR DINÂMICA ==========
function generateSidebar() {
  const controls = document.getElementById('editorControls');
  controls.innerHTML = '';

  // 🔹 Variáveis globais
  const rootVars = getRootVariables();
  const varSection = document.createElement('div');
  varSection.className = 'section';
  varSection.innerHTML = `<h3 onclick="toggleSection(this)">🎨 Variáveis Globais</h3>
    <div class="section-content"></div>`;
  const varContent = varSection.querySelector('.section-content');
  rootVars.forEach(([name, value]) => {
    const label = document.createElement('label');
    label.textContent = name.replace('--', '');
    const input = document.createElement('input');
    input.value = value;
    input.type = name.includes('cor') || name.includes('fundo') ? 'color' : 'text';
    input.oninput = () => {
      iframeDoc.documentElement.style.setProperty(name, input.value);
      saveDraft();
    };
    varContent.append(label, input);
  });
  controls.appendChild(varSection);

  // 🔹 Campos de edição
  const editables = iframeDoc.querySelectorAll('[data-edit]');
  const editSection = document.createElement('div');
  editSection.className = 'section';
  editSection.innerHTML = `<h3 onclick="toggleSection(this)">✏️ Conteúdo</h3>
    <div class="section-content"></div>`;
  const editContent = editSection.querySelector('.section-content');

  editables.forEach(el => {
    const id = el.dataset.edit;
    const label = document.createElement('label');
    label.textContent = id;

    if (el.tagName === 'IMG') {
      const btn = document.createElement('button');
      btn.textContent = 'Alterar imagem';
      btn.className = 'btn';
      btn.onclick = () => selectImage(el);
      editContent.append(label, btn);
    } else {
      const input = document.createElement('textarea');
      input.value = el.textContent.trim();
      input.oninput = () => {
        el.textContent = input.value;
        saveDraft();
      };
      editContent.append(label, input);
    }
  });

  controls.appendChild(editSection);
}

// ========== PEGAR VARIÁVEIS CSS ==========
function getRootVariables() {
  const style = getComputedStyle(iframeDoc.documentElement);
  const vars = [];
  for (let i = 0; i < style.length; i++) {
    const name = style[i];
    if (name.startsWith('--')) {
      vars.push([name, style.getPropertyValue(name).trim()]);
    }
  }
  return vars;
}

// ========== SELECIONAR IMAGEM ==========
function selectImage(img) {
  const upload = document.createElement('input');
  upload.type = 'file';
  upload.accept = 'image/*';
  upload.onchange = e => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
      img.src = ev.target.result;
      saveDraft();
    };
    reader.readAsDataURL(file);
  };
  upload.click();
}

// ========== SALVAR RASCUNHO ==========
function saveDraft() {
  currentHTML = iframeDoc.documentElement.outerHTML;
  localStorage.setItem('draftHTML', currentHTML);
}

// ========== DOWNLOAD DO SITE ==========
document.getElementById('downloadSite').onclick = async () => {
  const zip = new JSZip();
  zip.file('index.html', iframeDoc.documentElement.outerHTML);
  const blob = await zip.generateAsync({ type: 'blob' });
  saveAs(blob, `${currentTemplate}.zip`);
};

// ========== SALVAR NO BANCO ==========
document.getElementById('saveProject').onclick = async () => {
  try {
    const title = (iframeDoc && iframeDoc.title) ? iframeDoc.title : (document.title || 'Projeto sem título');
    const contentHtml = iframeDoc.documentElement.outerHTML;

    // coletar variáveis CSS (:root)
    const cssVars = {};
    const style = iframeDoc.defaultView.getComputedStyle(iframeDoc.documentElement);
    for (let i = 0; i < style.length; i++) {
      const name = style[i];
      if (name.startsWith('--')) {
        cssVars[name] = style.getPropertyValue(name).trim();
      }
    }

    const form = new FormData();
    // id pode ser '' para novo projeto — ProjectController cria e retorna id
    form.append('id', PROJECT_ID || '');
    form.append('title', title);
    form.append('template', currentTemplate || '');
    form.append('content_html', contentHtml); // padronizamos content_html no backend
    form.append('global_vars', JSON.stringify(cssVars));

   

    const res = await fetch('/projects/save', { method: 'POST', body: form });
    const data = await res.json();

    if (data.success) {
      // Se for criação (antes não havia PROJECT_ID), redireciona para editor?id=newId
      if (!PROJECT_ID && data.id) {
        window.location.href = `/editor?id=${data.id}`;
        return;
      }
      alert('✅ Projeto salvo com sucesso!');
    } else {
      alert('❌ Falha ao salvar: ' + (data.message || 'erro desconhecido'));
    }
  } catch (err) {
    alert('❌ Erro ao salvar: ' + err.message);
  }
};

// ========== ALTERAR TEMPLATE ==========
document.getElementById('loadTemplate').onclick = () => {
  currentTemplate = document.getElementById('templateSelect').value;
  loadTemplate(currentTemplate);
};

// ========== VISUALIZAR ==========
document.getElementById('preview').onclick = () => {
  const blob = new Blob([iframeDoc.documentElement.outerHTML], { type: 'text/html' });
  const url = URL.createObjectURL(blob);
  window.open(url, '_blank');
};

// ========== TOGGLE ==========
function toggleSection(el) {
  const content = el.nextElementSibling;
  content.style.display = content.style.display === 'block' ? 'none' : 'block';
}

window.addEventListener('DOMContentLoaded', () => {
  loadTemplate();
});





// ==============================
//  Variáveis globais
// ==============================
const iframe = document.getElementById('editorFrame');
let iframeDoc = null;
let currentHTML = '';
let currentTemplate = TEMPLATE_NAME || 'institucional';
let projectLoaded = false;

// ==============================
//  Inicialização
// ==============================
window.addEventListener('DOMContentLoaded', () => {
  loadTemplate(currentTemplate);
});

// ==============================
//  Carregar Template ou Projeto
// ==============================
async function loadTemplate(name) {
  iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

  // Se for um projeto existente, buscar o conteúdo salvo
  if (PROJECT_ID) {
    try {
      const res = await fetch(`/projects/get?id=${PROJECT_ID}`);
      const json = await res.json();

      if (json.success && json.data && json.data.content_html) {
        renderTemplate(json.data.content_html);

        // Aplicar variáveis globais salvas
        if (json.data.global_vars) {
          const vars = JSON.parse(json.data.global_vars);
          for (const [key, val] of Object.entries(vars)) {
            iframeDoc.documentElement.style.setProperty(key, val);
          }
        }

        projectLoaded = true;
        return;
      }
    } catch (err) {
      console.warn('Falha ao carregar projeto salvo:', err);
    }
  }

  // Se não tiver conteúdo salvo, carregar o template base
  try {
    const res = await fetch(`/templates/${name}.html`);
    const html = await res.text();
    renderTemplate(html);
    projectLoaded = true;
  } catch (err) {
    console.error('Erro ao carregar template base:', err);
    renderTemplate('<h1>Erro ao carregar template base.</h1>');
  }
}

// ==============================
//  Renderizar o conteúdo no iframe (corrigido)
// ==============================
function renderTemplate(html) {
  iframeDoc.open();
  iframeDoc.write(html);
  iframeDoc.close();

  // Aguarda o iframe terminar de carregar
  iframe.onload = () => {
    currentHTML = html;
    enableEditing();
  };
}
// ==============================
//  Tornar os elementos editáveis (corrigido)
// ==============================
function enableEditing() {
  // garante que o conteúdo já esteja disponível
  const editables = iframeDoc.querySelectorAll('[data-edit]');
  if (!editables.length) {
    console.warn('⚠️ Nenhum elemento com data-edit encontrado ainda, aguardando DOM...');
    setTimeout(enableEditing, 200); // tenta de novo depois de 200ms
    return;
  }

  console.log(`🎨 ${editables.length} elementos editáveis detectados.`);

  editables.forEach(el => {
    if (el.tagName === 'IMG') {
      el.style.cursor = 'pointer';
      el.title = 'Clique para trocar imagem';
      el.addEventListener('click', () => selectImage(el));
    } else {
      el.setAttribute('contenteditable', 'true');
      el.addEventListener('input', saveDraft);
    }
  });
}


// ==============================
//  Upload e substituição de imagens
// ==============================
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

// ==============================
//  Salvamento local (rascunho)
// ==============================
function saveDraft() {
  currentHTML = iframeDoc.documentElement.outerHTML;
  localStorage.setItem('draftHTML', currentHTML);
}

// ==============================
//  Salvar projeto no servidor
// ==============================
document.getElementById('saveProject').onclick = async () => {
  try {
    const title = iframeDoc.title || 'Projeto sem título';
    const contentHtml = iframeDoc.documentElement.outerHTML;

    // coletar variáveis globais CSS
    const cssVars = {};
    const style = iframeDoc.defaultView.getComputedStyle(iframeDoc.documentElement);
    for (let i = 0; i < style.length; i++) {
      const name = style[i];
      if (name.startsWith('--')) {
        cssVars[name] = style.getPropertyValue(name).trim();
      }
    }

    const form = new FormData();
    form.append('id', PROJECT_ID || '');
    form.append('title', title);
    form.append('template', currentTemplate);
    form.append('content_html', contentHtml);
    form.append('global_vars', JSON.stringify(cssVars));

    const res = await fetch('/projects/save', { method: 'POST', body: form });
    const data = await res.json();

    if (data.success) {
      if (!PROJECT_ID && data.id) {
        window.location.href = `/editor?id=${data.id}&template=${currentTemplate}`;
        return;
      }
      alert('✅ Projeto salvo com sucesso!');
    } else {
      alert('❌ Erro ao salvar: ' + (data.message || 'Erro desconhecido'));
    }
  } catch (err) {
    alert('❌ Falha ao salvar: ' + err.message);
  }
};

// ==============================
//  Preview e Download
// ==============================
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

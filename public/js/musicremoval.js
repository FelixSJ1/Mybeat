/* musicremoval.js
   Lógica do modal: abrir, preencher, fechar, e remoção visual.
*/
document.addEventListener('DOMContentLoaded', function(){
  const overlay = document.getElementById('overlay');
  const modalSong = document.getElementById('modalSong');
  const modalMeta = document.getElementById('modalMeta');
  const modalCover = document.getElementById('modalCover');
  const confirmBtn = document.getElementById('confirmBtn');
  let activeId = null;

  window.openModal = function(id){
    activeId = id;
    const row = document.querySelector('[data-id="'+id+'"]');
    if(!row) return;
    const title = row.querySelector('.track-title').innerText;
    const meta = row.querySelector('.track-meta').innerText;
    const cover = row.querySelector('.cover').innerText || '?';
    modalSong.textContent = title;
    modalMeta.textContent = meta;
    modalCover.textContent = cover;
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden','false');
    document.getElementById('cancelBtn').focus();
  };

  window.closeModal = function(){
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden','true');
    activeId = null;
  };

  confirmBtn.addEventListener('click', function(){
    if(!activeId) return;
    // Nesta versão demo não há conexão com DB; apenas remove visualmente.
    const el = document.querySelector('[data-id="'+activeId+'"]');
    if(el){
      el.style.transition = 'all .28s ease';
      el.style.opacity = '0';
      el.style.transform = 'translateX(-8px) scale(.98)';
      setTimeout(()=> el.remove(), 300);
    }
    confirmBtn.textContent = 'Removido ✓';
    confirmBtn.disabled = true;
    setTimeout(()=>{
      confirmBtn.textContent = 'Remover';
      confirmBtn.disabled = false;
      closeModal();
    }, 700);
  });

  // fechar clicando fora
  overlay.addEventListener('click', function(e){
    if(e.target === overlay) closeModal();
  });
  // esc para fechar
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape' && overlay.classList.contains('show')) closeModal();
  });
});
